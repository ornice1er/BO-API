<?php

namespace App\Jobs;

use App\Models\Prestation;
use App\Models\Project;
use App\Models\Requete;
use App\Services\PNSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloseProjectRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Nombre de tentatives avant échec définitif. */
    public int $tries = 3;

    /** Délai (s) entre tentatives : 10s, 30s, 60s. */
    public array $backoff = [10, 30, 60];

    /** Temps max d'exécution (s) — la boucle peut être longue. */
    public int $timeout = 300;

    protected $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Appelé quand le job échoue définitivement (après $tries).
     */
    public function failed(\Throwable $e): void
    {
        Log::error("CloseProjectRequests: échec définitif projet {$this->projectId} — " . $e->getMessage());
    }

    public function handle(): void
    {
        $project = Project::findOrFail($this->projectId);

        // Fichier de clôture obligatoire
        $closingFileUrl = $project->closing_filename
            ? Storage::disk('public')->url($project->closing_filename)
            : null;

        if (!$closingFileUrl) {
            Log::warning("CloseProjectRequests: projet {$this->projectId} sans fichier de clôture — abandon.");
            return;
        }

        $prestationCodes = is_array($project->prestations)
            ? $project->prestations
            : json_decode($project->prestations, true) ?? [];

        $prestations = Prestation::whereIn('code', $prestationCodes)
            ->where('is_group_delivered', true)
            ->get();

        foreach ($prestations as $prestation) {

            $requetes = Requete::where('project_id', $project->id)
                ->where('prestation_id', $prestation->id)
                ->get();

            $uniqueToken = encrypt([
                'project_id'    => $project->id,
                'prestation_id' => $prestation->id,
                'expires_at'    => now()->addDays(30)->toDateTimeString(),
            ]);

            $uniqueLink = \App\Utilities\Common::dedupeBaseUrl(route('project.closing.file', ['token' => $uniqueToken]));

            foreach ($requetes as $requete) {
                try {
                    $pnsService = new PNSService($requete->header, [
                        'data'     => null,
                        'message'  => "Publication d'arrêté de clôture demande : " . $requete->code,
                        'status'   => true,
                        'link'     => $uniqueLink,
                        'decision' => $prestation->decision,
                    ]);

                    $requete->filename = $uniqueLink;
                    $requete->save();

                    $pnsService->reply();

                } catch (\Exception $e) {
                    Log::error("CloseProjectRequests: erreur PNS requête {$requete->code} : " . $e->getMessage());
                }
            }
        }

        $project->update(['status' => 'closed']);
    }
}
