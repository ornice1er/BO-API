<?php

namespace App\Http\Controllers;

use App\Models\Etape;
use App\Models\MotifRejet;
use App\Models\PrestationStatus;
use App\Models\Requete;
use App\Models\RequeteEtapeLog;
use App\Models\Status;
use App\Models\WorkflowTransition;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * WorkflowStateController
 *
 * Expose l'état courant du workflow d'une requête et permet d'appliquer
 * une transition (validation, rejet, signature, etc.).
 *
 * Routes :
 *   GET  /requete/{id}/workflow-state      → getState()
 *   POST /requete/{id}/apply-transition    → applyTransition()
 *   GET  /requete/{id}/etape-logs          → getLogs()
 */
class WorkflowStateController extends Controller
{
    protected LogService $ls;

    public function __construct(LogService $ls)
    {
        $this->ls = $ls;
    }

    /**
     * Retourne l'étape courante, le statut, les transitions disponibles
     * et les motifs de rejet applicables pour la requête donnée.
     */
    public function getState(int $id)
    {
        try {
            $requete = Requete::with('prestation')->findOrFail($id);

            $currentEtape  = $requete->current_etape_id
                ? Etape::find($requete->current_etape_id)
                : null;

            $currentStatus = $requete->current_status_id
                ? Status::find($requete->current_status_id)
                : null;

            // Transitions disponibles depuis l'étape courante, pour cette prestation
            $transitions = [];
            if ($currentEtape && $requete->prestation_id) {
                $transitions = WorkflowTransition::with(['etapeTo', 'statusResult'])
                    ->where('prestation_id', $requete->prestation_id)
                    ->where('etape_from_id', $currentEtape->id)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->get();
            }

            // Motifs de rejet pour l'étape courante (globaux ou spécifiques à la prestation)
            $motifsRejet = [];
            if ($currentEtape && $requete->prestation_id) {
                $motifsRejet = MotifRejet::where(function ($q) use ($requete) {
                        $q->where('prestation_id', $requete->prestation_id)
                          ->orWhereNull('prestation_id');
                    })
                    ->where(function ($q) use ($currentEtape) {
                        $q->where('etape_id', $currentEtape->id)
                          ->orWhereNull('etape_id');
                    })
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->get();
            }

            // SLA : calcul des jours restants si etape_started_at est défini
            $slaInfo = null;
            if ($currentEtape && $currentEtape->sla_days && $requete->etape_started_at) {
                $deadline     = \Carbon\Carbon::parse($requete->etape_started_at)->addDays($currentEtape->sla_days);
                $daysLeft     = now()->diffInDays($deadline, false);
                $slaInfo = [
                    'sla_days'    => $currentEtape->sla_days,
                    'deadline'    => $deadline->toDateString(),
                    'days_left'   => (int) $daysLeft,
                    'is_overdue'  => $daysLeft < 0,
                ];
            }

            $result = [
                'requete'        => $requete,
                'current_etape'  => $currentEtape,
                'current_status' => $currentStatus,
                'transitions'    => $transitions,
                'motifs_rejet'   => $motifsRejet,
                'sla_info'       => $slaInfo,
            ];

            $this->ls->trace([
                'action_name' => 'Consultation état workflow',
                'description' => "Requête #{$id}",
            ]);

            return Common::success('État du workflow récupéré', $result);

        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Applique une transition au workflow de la requête :
     *  - met à jour current_etape_id et current_status_id
     *  - crée une entrée dans requete_etape_logs
     */
    public function applyTransition(Request $request, int $id)
    {
        $request->validate([
            'transition_id'  => 'required|integer|exists:workflow_transitions,id',
            'comment'        => 'nullable|string|max:2000',
            'motif_rejet_id' => 'nullable|integer|exists:motifs_rejet,id',
        ]);

        try {
            $requete    = Requete::findOrFail($id);
            $transition = WorkflowTransition::with(['etapeTo', 'statusResult'])->findOrFail($request->transition_id);

            // Vérifier cohérence prestation
            if ((int) $transition->prestation_id !== (int) $requete->prestation_id) {
                return Common::error('Cette transition n\'appartient pas à la prestation de cette requête.', []);
            }

            // Vérifier que l'étape de départ correspond à l'étape courante
            if ((int) $transition->etape_from_id !== (int) $requete->current_etape_id) {
                return Common::error('Cette transition ne part pas de l\'étape courante de la requête.', []);
            }

            // Vérifier que le statut résultant est bien associé à la prestation
            $valid = PrestationStatus::where('prestation_id', $requete->prestation_id)
                ->where('status_id', $transition->status_result_id)
                ->exists();

            if (!$valid) {
                throw new \Exception("Ce statut n'est pas autorisé pour cette prestation.");
            }

            $previousEtapeId = $requete->current_etape_id;

            // Mettre à jour la requête
            $requete->current_etape_id  = $transition->etape_to_id;
            $requete->current_status_id = $transition->status_result_id;
            $requete->etape_started_at  = now();
            $requete->save();

            // Construire les métadonnées du log
            $metadata = [];
            if ($request->motif_rejet_id) {
                $motif = MotifRejet::find($request->motif_rejet_id);
                if ($motif) {
                    $metadata['motif_rejet_id']  = $motif->id;
                    $metadata['motif_rejet_code'] = $motif->code;
                    $metadata['motif_rejet']      = $motif->libelle;
                    $metadata['is_final']         = $motif->is_final;
                }
            }

            // Créer le log de transition
            RequeteEtapeLog::create([
                'requete_id'             => $requete->id,
                'workflow_transition_id' => $transition->id,
                'etape_from_id'          => $previousEtapeId,
                'etape_to_id'            => $transition->etape_to_id,
                'status_id'              => $transition->status_result_id,
                'triggered_by'           => Auth::id(),
                'triggered_by_type'      => 'agent',
                'comment'                => $request->comment,
                'metadata'               => !empty($metadata) ? $metadata : null,
                'transitioned_at'        => now(),
            ]);

            $this->ls->trace([
                'action_name' => 'Transition workflow appliquée',
                'description' => "Requête #{$id} — transition #{$transition->id} ({$transition->condition_type})",
            ]);

            return Common::success('Transition appliquée avec succès', [
                'requete'    => $requete->fresh(['prestation']),
                'transition' => $transition,
            ]);

        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Retourne l'historique complet des transitions d'une requête,
     * du plus récent au plus ancien.
     */
    public function getLogs(int $id)
    {
        try {
            $logs = RequeteEtapeLog::with([
                    'etapeFrom',
                    'etapeTo',
                    'status',
                    'triggeredBy',
                    'transition',
                ])
                ->where('requete_id', $id)
                ->orderByDesc('transitioned_at')
                ->get();

            return Common::success('Historique des transitions récupéré', $logs);

        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }
}
