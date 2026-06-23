<?php

namespace App\Http\Repositories;

use App\Models\Project;
use App\Models\Requete;
use App\Models\Prestation;
use App\Services\PNSService;
use App\Traits\Repository;
use App\Utilities\FileStorage;
use Str,Storage;

class ProjectRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Project
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Project::class);
    }

    /**
     * Check if exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all elements
     */
    public function getAll($request)
    {
$per_page = 10;

$req = Project::ignoreRequest(['per_page', 'prestation_codes', 'exclude_closed']) // ✅
    ->orderByDesc('created_at');

if ($request->has('prestation_codes')) {
    $codes = array_map('trim', explode(',', $request->get('prestation_codes')));

    $req->where(function($q) use ($codes) {
        foreach ($codes as $code) {
            $q->orWhereJsonContains('prestations', $code);
        }
    });
}

// Association de demande : exclure les projets clos (cohérence back ↔ front)
if ($request->boolean('exclude_closed')) {
    $req->where(function ($q) {
        $q->whereNull('status')->orWhere('status', '!=', 'closed');
    });
}

if (array_key_exists('per_page', $request->all())) {
    $per_page = $request['per_page'];
    return $req->paginate($per_page);
} else {
    return $req->get();
}
    }

    /**
     * Get an element
     */
    public function get($id)
    {
         return $this->with([
        'requetes.currentStatus'
    ])->findOrFail($id);
    }

    /**
     * To store model
     */
    public function makeStore($data): Project
    {

        if (request()->hasFile('file')) {
            $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['filename'] = 'projects/'.$filename;
                    unset( $data['file']);

        }

         if (request()->hasFile('closing_filename')) {
            $filename = FileStorage::setFile('public', request()->file('closing_filename'), 'projects', Str::slug($data['title'].'.'.time()));
            $data['closing_filename'] = 'projects/'.$filename;

        }

        $model = new Project($data);
        $model->save();

        return $model;
    }

    /**
     * To update model
     */
    public function makeUpdate($id, $data): Project
    {
$model = Project::findOrFail($id);

if (request()->hasFile('file')) {
    FileStorage::deleteFile('public', $model->filename, 'projects');
    $filename = FileStorage::setFile('public', request()->file('file'), 'projects', Str::slug($data['title'].'.'.time()));
    $data['filename'] = 'projects/'.$filename;
    unset($data['file']);
}

if (request()->hasFile('closing_filename')) {
    FileStorage::deleteFile('public', $model->closing_filename, 'projects');
    $filename = FileStorage::setFile('public', request()->file('closing_filename'), 'projects', Str::slug($data['title'].'.'.time()));
    $data['closing_filename'] = 'projects/'.$filename;
}

$model->update($data);

// ──────────────────────────────────────────
// Si le projet passe au statut "closed"
// ──────────────────────────────────────────
if (isset($data['status']) && $data['status'] === 'closed') {

    // Fichier de clôture obligatoire
    $closingFileUrl = $model->closing_filename
        ? Storage::disk('public')->url($model->closing_filename)
        : null;

    if (!$closingFileUrl) {
        return $model;
    }

    $prestationCodes = is_array($model->prestations)
        ? $model->prestations
        : json_decode($model->prestations, true) ?? [];

    $prestations = Prestation::whereIn('code', $prestationCodes)
        ->where('is_group_delivered', true)
        ->get();

    foreach ($prestations as $prestation) {

        // ✅ Récupération directe via project_id et prestation_id
        $requetes = Requete::where('project_id', $model->id)
            ->where('prestation_id', $prestation->id)
            ->get();

        $uniqueToken = encrypt([
            'project_id'    => $model->id,
            'prestation_id' => $prestation->id,
            'expires_at'    => now()->addDays(30)->toDateTimeString(),
        ]);

        $uniqueLink = route('project.closing.file', ['token' => $uniqueToken]);

        foreach ($requetes as $requete) {
            try {
                $pnsService = new PNSService($requete->header, [
                    'data'     => null,
                    'message'  => "Publication d'arrêté de clôture demande : " . $requete->code,
                    'status'   => true,
                    'link'     => $uniqueLink,
                    'decision' => $prestation->decision,
                ]);
                $requete->filename=$uniqueLink;
                 $requete->save();
                $pnsService->reply();

            } catch (\Exception $e) {
                \Log::error("Erreur PNS requête {$requete->code} : " . $e->getMessage());
            }
        }
    }
}

return $model;
    }

    /**
     * To delete model
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * To get all latest
     */
    public function getlatest()
    {
        return $this->latest()->get();
    }

    /**
     * Get an element
     */
    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    public function search($term)
    {
        $query = Project::query(); // Commencer avec une requête vide
        $attrs = ['title', 'description'];
        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Retourner les résultats
    }

    /**
     * Get project with requests
     */
    public function getWithRequests($id)
    {
        return Project::with([
            'requetes.prestation',
            'requetes.currentStatus',
            'requetes.currentEtape',
        ])->findOrFail($id);
    }

    /**
     * Add request IDs to project
     */
    public function addRequests($projectId, $requestIds)
    {
        $project = Project::findOrFail($projectId);
        Requete::whereIn('id', $requestIds)->update(['project_id' => $projectId]);
        return $project->load('requetes');
    }

    /**
     * Close project
     */
    public function close($id)
    {
        $project = Project::findOrFail($id);
        $project->update(['status' => 'closed']);
        return $project;
    }
}