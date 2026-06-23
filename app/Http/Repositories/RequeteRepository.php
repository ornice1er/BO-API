<?php

namespace App\Http\Repositories;

use App\Models\Requete;
use App\Models\Prestation;
use App\Models\UniteAdmin;
use App\Models\Reponse;
use App\Models\WorkflowTransition;
use App\Models\RequeteEtapeLog;
use App\Models\EtapeVisibilite;
use App\Models\DocumentActe;
use App\Models\DocumentCircuitEtape;
use App\Models\EtapeNotification;
use App\Models\Agenda;
use App\Traits\Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Services\PNSService;

class RequeteRepository
{
    use Repository;

    protected $model;

    public function __construct()
    {
        $this->model = app(Requete::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LECTURE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Banette principale d'un agent selon son rôle et les visibilités configurées.
     * Remplace getByPrestation() qui filtrait par affectations.
     */
  public function getBanette(string $prestationCode): \Illuminate\Support\Collection
{
    $prestation       = Prestation::where('code', $prestationCode)->firstOrFail();
    $user             = Auth::user();
    $userRoles        = $user->getRoleNames();
    $userUniteAdminId = $user->agent?->unite_admin_id;

    // Périmètre géographique de l'unité de l'agent (CS ou DDEMP)
    $userUA = $userUniteAdminId
        ? UniteAdmin::find($userUniteAdminId)
        : null;

    $transitionIds = EtapeVisibilite::whereIn('role_name', $userRoles)
        ->where('can_act', true)
        ->where(function ($q) use ($userUniteAdminId) {
            $q->where('scope_type', 'requete')
              ->orWhere(function ($q2) use ($userUniteAdminId) {
                  $q2->where('scope_type', 'unite_admin')
                     ->where('unite_admin_id', $userUniteAdminId);
              });
        })
        ->pluck('workflow_transition_id');

        // Bannette = dossiers dont l'étape courante est le POINT DE DÉPART
        // (etape_from) d'une transition que l'agent a le droit d'exécuter.
        if (request()->nature=='validation') {
             $etapeIds = WorkflowTransition::whereIn('id', $transitionIds)
             ->where('prestation_id', $prestation->id)
            ->whereIn('condition_type', ["validation","prevalidation","paraphe","choix_sortie","visite"])
            ->pluck('etape_from_id');
        } else {
        $etapeIds = WorkflowTransition::whereIn('id', $transitionIds)
        ->where('prestation_id', $prestation->id)
        ->where('condition_type', request()->nature)
        ->pluck('etape_from_id');
        }

    return Requete::with([
            'currentEtape',
            'currentStatus',
            'prestation',
            'files',
            'parcours',
            'lastLog',
        ])
        ->where('prestation_id', $prestation->id)
        ->whereIn('current_etape_id', $etapeIds)
        ->where('isTreated', false)
        ->where('isDeclined', false)
        // ── Filtre géographique ──────────────────────────────────────────────
        // CS (unité communale avec municipality_id configuré) : commune exacte
        ->when($userUA?->municipality_id, function ($q) use ($userUA) {
            $q->where('municipality_id', $userUA->municipality_id);
        })
        // Unité à périmètre départemental (DDEMP ou CS ancien sans municipality_id) :
        // - requêtes directement au niveau département
        // - OU requêtes communales dont la commune appartient à ce département
        // (les requêtes communales ont department_id=null car exclusif avec municipality_id)
        ->when(!$userUA?->municipality_id && $userUA?->department_id, function ($q) use ($userUA) {
            $munIds = \App\Models\Municipality::where('department_id', $userUA->department_id)
                ->pluck('id');
            $q->where(function ($sub) use ($userUA, $munIds) {
                $sub->where('department_id', $userUA->department_id)
                    ->orWhereIn('municipality_id', $munIds);
            });
        })
        // ────────────────────────────────────────────────────────────────────
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($requete) {
            $requete->sla_restant        = $this->calculerSlaRestant($requete);
            $requete->dernier_acteur     = $requete->lastLog?->triggeredBy?->name ?? 'Système';
            $requete->derniere_action_at = $requete->lastLog?->transitioned_at;
            return $requete;
        });
}

    /**
     * Toutes les demandes d'une prestation (vue admin / superviseur).
     */
    public function getAll($request): mixed
    {
        $per_page = $request['per_page'] ?? 10;

        $query = Requete::with([
                'currentEtape',
                'currentStatus',
                'prestation',
                'files',
                'parcours',
            ])
            ->orderByDesc('created_at');

        if (isset($request['prestation_code'])) {
            $prestation = Prestation::where('code', $request['prestation_code'])->first();
            if ($prestation) {
                $query->where('prestation_id', $prestation->id);
            }
        }

        return isset($request['per_page'])
            ? $query->paginate($per_page)
            : $query->get();
    }

    /**
     * Détail complet d'une requête.
     */
    public function getOne(array $data): Requete
    {
        return Requete::with([
                'currentEtape',
                'currentStatus',
                'prestation',
                'files',
                'parcours',
                'project',
                'agendas',
                'planningSlot.uniteAdmin',
                'documentActes.docProduit',
                'documentActes.currentCircuitStep',
                'documentActes.logs',
                'requeteEtapeLogs.etapeFrom',
                'requeteEtapeLogs.etapeTo',
                'requeteEtapeLogs.status',
                'requeteEtapeLogs.status',
                'requeteEtapeLogs.triggeredBy',
                'requeteEtapeLogs.transition.visibilites.ua.entite',
            ])
            ->where('code', $data['code'])
            ->firstOrFail();
    }

    /**
     * Suivi public par le requérant (code de suivi uniquement).
     */
    public function getSuivi(string $code): array
    {
        $req = Requete::with(['currentEtape', 'currentStatus', 'prestation'])
            ->where('code', $code)
            ->firstOrFail();

        return [
            'code'           => $req->code,
            'prestation'     => $req->prestation->name,
            'statut'         => $req->currentStatus?->name,
            'etape_courante' => $req->currentEtape?->name,
            'date_depot'     => $req->created_at->format('d/m/Y'),
            'sla_restant'    => $this->calculerSlaRestant($req),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MOTEUR DE WORKFLOW — méthode centrale
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Avancer une requête vers la prochaine étape.
     *
     * @param  Requete  $requete
     * @param  string   $conditionType  auto | validation | rejet | complement | signature | paraphe | prevalidation | cloture
     * @param  array    $options        ['comment' => '', 'motif_id' => null, 'metadata' => []]
     */
    public function avancerWorkflow(
        Requete $requete,
        string  $conditionType,
        array   $options = []
    ): Requete {
        DB::beginTransaction();

        try {
            // 1. Trouver la transition applicable
            $transition = WorkflowTransition::where('prestation_id',  $requete->prestation_id)
                ->where('etape_from_id',  $requete->current_etape_id)
                ->where('condition_type', $conditionType)
                ->where('is_active',      true)
                ->orderBy('order')
                ->firstOrFail();

            $user = Auth::user();

            // 2. Mettre à jour la requête
            $requete->current_etape_id  = $transition->etape_to_id;
            $requete->current_status_id = $transition->status_result_id;
            $requete->etape_started_at  = now();

            // Rétrocompatibilité ancien système (colonne `status` conservée)
            // NB : `pris_en_charge` supprimée (cleanup 2026_05_21) — l'info est portée par RequeteEtapeLog
            $requete->status       = $transition->status_result_id;

            if ($transition->etape->is_terminal ?? false) {
                $requete->isTreated  = true;
                $requete->isFinished = true;
                $requete->closed_at  = now();
            }

            if ($conditionType === 'cloture') {
                $requete->isDeclined = true;
            }

            $requete->save();

            // 3. Journaliser la transition
            $logMetadata = $options['metadata'] ?? [];
            if (!empty($options['note_file_path'])) {
                $logMetadata['note_file_path'] = $options['note_file_path'];
            }

            RequeteEtapeLog::create([
                'requete_id'             => $requete->id,
                'workflow_transition_id' => $transition->id,
                'etape_from_id'          => $transition->etape_from_id,
                'etape_to_id'            => $transition->etape_to_id,
                'status_id'              => $transition->status_result_id,
                'triggered_by'           => $user?->id,
                'triggered_by_type'      => $user ? 'agent' : ((($logMetadata['source'] ?? null) === 'pns') ? 'usager' : 'système'),
                'comment'                => $options['comment'] ?? null,
                'metadata'               => !empty($logMetadata) ? $logMetadata : null,
                'transitioned_at'        => now(),
                'created_at'             => now(),
            ]);

            // Slot PNS : créer l'agenda automatiquement si pas encore fait
            if ($requete->planning_slot_id) {
                $this->creerAgendaDepuisSlot($requete);
            }

            if ($transition->can_act_pns) {
                $isGroupDelivered  = (bool) ($requete->prestation->is_group_delivered ?? false);
                $isTerminal        = (bool) ($transition->etape->is_terminal ?? false);
                $isFavorable       = !in_array(
                    $transition->statusResult->short_name ?? '',
                    ['rejete', 'rejete_clos', 'cloture', 'annule']
                );

                // Pour une délivrance groupée, l'étape terminale favorable est traitée
                // en masse côté projet ; on ne notifie pas le PNS individuellement.
                $skipPns = $isGroupDelivered && $isTerminal && $isFavorable;

                if (!$skipPns) {
                    $pnsService = new PNSService($requete->header, [
                        "data"     => null,
                        "message"  => "Mise à jour de votre demande : " . $requete->code,
                        "status"   => true,
                        "decision" => $transition->decision,
                        "link"     => $options['link'] ?? null,
                        "comment"  => $options['comment'] ?? null,
                    ]);
                    $result = $pnsService->reply();
                }
            }
              

                
                // if (!$result->successful()) {
                //     Log::error("Échec de la notification PNS pour la requête {$requete->code}", [
                //         'response_status' => $result->status(),
                //         'response_body'   => $result->body(),
                //     ]);
                // }
            // 4. Déclencher les notifications configurées
            $this->declencherNotifications($transition, $requete);

            DB::commit();

            return $requete->fresh(['currentEtape', 'currentStatus']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('avancerWorkflow error: ' . $e->getMessage(), [
                'requete_id'     => $requete->id,
                'condition_type' => $conditionType,
            ]);
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS MÉTIER
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * TC-18 — Prise en charge par l'agent (remplace prendreEnCharge).
     */
    public function prendreEnCharge(int $id): Requete
    {
        $requete = Requete::findOrFail($id);
        return $this->avancerWorkflow($requete, 'validation', [
            'comment' => 'Prise en charge par ' . Auth::user()?->name,
        ]);
    }

    /**
     * TC-19/TC-21 — Valider ou rejeter avec motif.
     */
    public function traiterDemande(int $id, string $decision, array $options = []): Requete
    {
        $requete = Requete::findOrFail($id);

        $condition = match($decision) {
            'valider'            => 'validation',
            'rejeter'            => 'rejet',
            'cloturer'           => 'cloture',
            'completer'          => 'complement',
            'signer'             => 'signature',
            'parapher'           => 'paraphe',
            'prevalider'         => 'prevalidation',
            'retour_correction'  => 'correction',
            default              => throw new \InvalidArgumentException("Décision inconnue : {$decision}"),
        };

        return $this->avancerWorkflow($requete, $condition, $options);
    }

    /**
     * TC-25/TC-31/TC-34 — Action sur le circuit documentaire (paraphe, signature, prévalidation).
     */
public function traiterDocument(int $acteId, string $action, array $options = []): DocumentActe
{
    DB::beginTransaction();

    try {
        $acte = DocumentActe::with(['currentCircuitStep', 'requete'])->findOrFail($acteId);
        $user = Auth::user();

        $step = $acte->currentCircuitStep;

        // Vérifier que c'est bien le rôle de l'utilisateur qui doit agir
        if (!$user->hasRole($step->role_name)) {
            throw new \Exception("Rôle {$step->role_name} requis pour cette action.");
        }

        // Logger l'action sur le document
        \App\Models\DocumentActeLog::create([
            'document_acte_id' => $acte->id,
            'circuit_step_id'  => $step->id,
            'action'           => $action,
            'triggered_by'     => $user->id,
            'role_name'        => $step->role_name,
            'comment'          => $options['comment'] ?? null,
            'acted_at'         => now(),
            'created_at'       => now(),
        ]);

        // Avancer vers l'étape suivante du circuit
        $nextStep = \App\Models\DocumentCircuitEtape::where('doc_produit_id', $acte->doc_produit_id)
            ->where('order', '>', $step->order)
            ->orderBy('order')
            ->first();

        if ($nextStep) {
            // Étape suivante dans le circuit
            $acte->current_circuit_step_id = $nextStep->id;
            $acte->status = 'en_circuit';
        } else {
            // Circuit terminé
            $acte->status       = 'complet';
            $acte->completed_at = now();
        }
        $acte->save();

        // ✅ Mettre à jour le statut de la requête via short_name
        if ($step->requete_status_after) {
            $nouveauStatut = \App\Models\Status::where('short_name', $step->requete_status_after)
                ->first();

            if ($nouveauStatut) {
                $acte->requete->current_status_id = $nouveauStatut->id;
                $acte->requete->status            = $nouveauStatut->id;
                $acte->requete->save();
            } else {
                \Log::warning("Status non trouvé pour short_name: {$step->requete_status_after}");
            }
        }

        // ✅ Mettre à jour current_etape_id de la requête selon l'étape du circuit
        if ($nextStep) {
            // Trouver la transition workflow correspondant à ce rôle
            $transition = \App\Models\WorkflowTransition::where('prestation_id', $acte->requete->prestation_id)
                ->where('condition_type', $action)
                ->whereHas('etapeFrom', fn($q) =>
                    $q->where('id', $acte->requete->current_etape_id)
                )
                ->first();

            if ($transition) {
                $acte->requete->current_etape_id = $transition->etape_to_id;
                $acte->requete->save();

                // Journaliser la transition workflow
                \App\Models\RequeteEtapeLog::create([
                    'requete_id'             => $acte->requete->id,
                    'workflow_transition_id' => $transition->id,
                    'etape_from_id'          => $transition->etape_from_id,
                    'etape_to_id'            => $transition->etape_to_id,
                    'status_id'              => $nouveauStatut?->id ?? $acte->requete->current_status_id,
                    'triggered_by'           => $user->id,
                    'triggered_by_type'      => 'agent',
                    'comment'                => $options['comment'] ?? null,
                    'transitioned_at'        => now(),
                    'created_at'             => now(),
                ]);
            } else {
                // Circuit terminé
                $acte->status                  = 'complet';
                $acte->completed_at            = now();
                $acte->current_circuit_step_id = null; // ← ajouter
            }
        }

        DB::commit();
        return $acte->fresh(['currentCircuitStep', 'logs']);

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('traiterDocument error: ' . $e->getMessage(), ['acte_id' => $acteId]);
        throw $e;
    }
}

    /**
     * Correction d'une demande rejetée par le requérant (FA2 / FA1).
     */
    public function corrigerDemande(int $id, array $data): Requete
    {
        $requete = Requete::findOrFail($id);

        // Mettre à jour les données corrigées
        if (isset($data['step_contents'])) {
            $requete->step_contents = $data['step_contents'];
        }
        if (isset($data['step_data'])) {
            $requete->step_data = $data['step_data'];
        }
        $requete->save();

        return $this->avancerWorkflow($requete, 'complement', [
            'comment' => 'Complément soumis par le requérant',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VÉRIFICATION DE COMPLÉTUDE DU DOSSIER
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Vérifie si toutes les pièces obligatoires sont fournies et valides.
     * Utilise la jointure etape_documents ↔ requete_files (migration 014).
     */
    public function verifierCompletude(Requete $requete): array
    {
        $pieces = DB::select("
            SELECT
                ed.id,
                ed.name,
                ed.slug,
                ed.is_required,
                rf.id          AS fichier_id,
                rf.is_valid,
                rf.rejection_reason
            FROM etape_documents ed
            LEFT JOIN requete_files rf
                   ON rf.etape_document_id = ed.id
                  AND rf.requete_id = ?
            WHERE ed.prestation_id = ?
              AND ed.etape_id      = ?
            ORDER BY ed.order
        ", [$requete->id, $requete->prestation_id, $requete->current_etape_id]);

        $manquantes = array_filter($pieces, fn($p) => $p->is_required && !$p->fichier_id);
        $invalides  = array_filter($pieces, fn($p) => $p->fichier_id && !$p->is_valid);

        return [
            'complet'    => empty($manquantes) && empty($invalides),
            'manquantes' => array_values($manquantes),
            'invalides'  => array_values($invalides),
            'toutes'     => $pieces,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PLANNING SLOT → AGENDA
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crée automatiquement un Agenda depuis le créneau PNS sélectionné.
     * Idempotent : n'agit que si le slot n'est pas encore lié à un agenda.
     * Appelé à l'intérieur de la transaction avancerWorkflow.
     */
    protected function creerAgendaDepuisSlot(Requete $requete): void
    {
        $slot = \App\Models\PlanningSlot::lockForUpdate()->find($requete->planning_slot_id);

        if (!$slot || $slot->agenda_id !== null) {
            return; // Déjà traité ou slot introuvable
        }

        $dateStart = \Carbon\Carbon::parse($slot->slot_date . ' ' . $slot->heure_debut);
        $dateEnd   = $dateStart->copy()->addMinutes($slot->duration_minutes ?? 20);

        $agenda = \App\Models\Agenda::create([
            'requete_id'       => $requete->id,
            'user_id'          => Auth::id(),
            'title'            => $requete->prestation->name ?? 'RDV',
            'date_start'       => $dateStart,
            'date_end'         => $dateEnd,
            'duration_minutes' => $slot->duration_minutes ?? 20,
            'session_type'     => $slot->session_type,
            'rdv_type'         => 'premier_rdv',
            'from'             => 'Usager',
            'status'           => 'Ouvert',
            'priority'         => 'Moyenne',
        ]);

        // Lier le slot à l'agenda et compter la réservation
        $slot->update([
            'agenda_id'    => $agenda->id,
            'slots_booked' => $slot->slots_booked + 1,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // NOTIFICATIONS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Déclenche les notifications configurées pour une transition.
     */
    protected function declencherNotifications(WorkflowTransition $transition, Requete $requete): void
    {
        $notifications = EtapeNotification::where('workflow_transition_id', $transition->id)
            ->where('is_active', true)
            ->get();

        foreach ($notifications as $notif) {
            try {
                $destinataire = match($notif->recipient_type) {
                    'requérant'  => $requete->email,
                    'agent'      => $this->getAgentEmail($requete, $transition),
                    'ministre'   => config('mail.ministre_email'),
                    'unite_admin'=> $this->getUniteAdminEmail($transition),
                    default      => null,
                };

                if (!$destinataire) continue;
                if ($destinataire!="requérant"){
                       match($notif->channel) {
                    'email' => Mail::send(
                        $notif->template_key,
                        ['requete' => $requete, 'extra' => $notif->extra_data],
                        fn($m) => $m->to($destinataire)
                                    ->subject("Mise à jour de votre demande {$requete->code}")
                    ),
                    'sms', 'whatsapp' => Log::info("Notification {$notif->channel} à envoyer", [
                        'destinataire' => $destinataire,
                        'template'     => $notif->template_key,
                    ]),
                    default => null,
                };
                }

             

            } catch (\Throwable $e) {
                // Ne pas bloquer le workflow si une notification échoue
                Log::error("Notification échouée: {$e->getMessage()}", [
                    'notif_id'   => $notif->id,
                    'requete_id' => $requete->id,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITAIRES
    // ─────────────────────────────────────────────────────────────────────────

    protected function calculerSlaRestant(Requete $requete): ?int
    {
        if (!$requete->etape_started_at || !$requete->currentEtape?->sla_days) {
            return null;
        }
        $joursEcoules = Carbon::parse($requete->etape_started_at)->diffInDays(now());
        return max(0, $requete->currentEtape->sla_days - $joursEcoules);
    }

    protected function getAgentEmail(Requete $requete, WorkflowTransition $transition): ?string
    {
        // Trouver l'agent de l'unité admin associée à l'étape cible
        return \App\Models\User::whereHas('agent.uniteAdmin', function ($q) use ($transition) {
            $q->where('id', $transition->etape->unite_admin_id);
        })->value('email');
    }

    protected function getUniteAdminEmail(WorkflowTransition $transition): ?string
    {
        return UniteAdmin::find($transition->etapeFrom->unite_admin_id)?->email;
    }

    /**
     * Vérifier si l'utilisateur courant peut agir sur cette requête
     * selon les visibilités configurées.
     */
   public function peutAgir(Requete $requete): bool
{
    $user             = Auth::user();
    $userRoles        = $user->getRoleNames();
    $userUniteAdminId = $user->agent?->unite_admin_id;
 
    return EtapeVisibilite::whereIn('role_name', $userRoles)
        ->where('can_act', true)
        ->where(function ($q) use ($userUniteAdminId) {
            $q->where('scope_type', 'requete')
              ->orWhere(function ($q2) use ($userUniteAdminId) {
                  $q2->where('scope_type', 'unite_admin')
                     ->where('unite_admin_id', $userUniteAdminId);
              });
        })
        ->whereHas('workflowTransition', function ($q) use ($requete) {
            // La visibilité doit porter sur la transition qui PART de l'étape
            // courante (l'action que l'agent s'apprête à exécuter), pas sur
            // celle qui y a mené (etape_to). Sinon un acteur garderait le droit
            // d'agir juste parce qu'il a effectué la transition entrante.
            $q->where('prestation_id', $requete->prestation_id)
              ->where('etape_from_id', $requete->current_etape_id);
        })
        ->exists();
}

    // ─────────────────────────────────────────────────────────────────────────
    // RÉGRESSION ADMIN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne la liste des étapes précédentes disponibles pour régression,
     * déduites de l'historique des transitions (requete_etape_logs).
     */
    public function getEtapesPrecedentes(Requete $requete): array
    {
        return RequeteEtapeLog::where('requete_id', $requete->id)
            ->whereNotNull('etape_from_id')
            ->with('etapeFrom')
            ->orderByDesc('transitioned_at')
            ->get()
            ->unique('etape_from_id')
            ->filter(fn($log) => $log->etape_from_id !== $requete->current_etape_id)
            ->map(fn($log) => [
                'id'              => $log->etapeFrom->id,
                'name'            => $log->etapeFrom->name,
                'transitioned_at' => $log->transitioned_at,
                'log_id'          => $log->id,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Régresse la requête vers une étape précédente (action admin uniquement).
     * Restaure le statut qu'avait la requête à son arrivée à cette étape.
     */
    public function regresser(Requete $requete, int $etapeId, ?string $comment = null): Requete
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();

            // Retrouver le log d'arrivée à l'étape cible pour restaurer le bon statut
            $logArrivee = RequeteEtapeLog::where('requete_id', $requete->id)
                ->where('etape_to_id', $etapeId)
                ->orderByDesc('transitioned_at')
                ->first();

            \App\Models\Etape::findOrFail($etapeId);

            $etapeFromId = $requete->current_etape_id; // capturer avant modification

            $requete->current_etape_id  = $etapeId;
            $requete->current_status_id = $logArrivee?->status_id ?? $requete->current_status_id;
            $requete->status            = $logArrivee?->status_id ?? $requete->status;
            $requete->etape_started_at  = now();
            $requete->isTreated         = false;
            $requete->isFinished        = false;
            $requete->isDeclined        = false;
            $requete->closed_at         = null;
            $requete->save();

            RequeteEtapeLog::create([
                'requete_id'             => $requete->id,
                'workflow_transition_id' => null,
                'etape_from_id'          => $etapeFromId,
                'etape_to_id'            => $etapeId,
                'status_id'              => $requete->current_status_id,
                'triggered_by'           => $user->id,
                'triggered_by_type'      => 'admin',
                'comment'                => $comment ?? 'Régression administrative',
                'transitioned_at'        => now(),
                'created_at'             => now(),
            ]);

            DB::commit();
            return $requete->fresh(['currentEtape', 'currentStatus']);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTHODES CONSERVÉES POUR RÉTROCOMPATIBILITÉ
    // ─────────────────────────────────────────────────────────────────────────

    public function get($id): Requete
    {
        return Requete::with(['currentEtape', 'currentStatus', 'prestation', 'files', 'parcours'])
            ->findOrFail($id);
    }

    public function makeStore(array $data): Requete
    {
        return Requete::create($data);
    }

    public function makeUpdate($id, array $data): Requete
    {
        $model = Requete::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function makeDestroy($id): bool
    {
        return Requete::findOrFail($id)->delete();
    }

    public function ifExist($id): ?Requete
    {
        return $this->find($id);
    }

    /**
     * @deprecated Utiliser getBanette() à la place
     */
    public function getByPrestation($data): \Illuminate\Support\Collection
    {
        return $this->getBanette($data['code']);
    }

    /**
     * @deprecated Utiliser getAll() à la place
     */
public function getByPrestationAll($data): \Illuminate\Support\Collection
{
    $prestation = Prestation::where('code', $data['code'])->firstOrFail();

    return Requete::with([
            'currentEtape',
            'currentStatus',
            'prestation',
            'files',
            'parcours',
            'lastLog.triggeredBy',
            'lastLog.etapeFrom',
            'lastLog.etapeTo',
            'lastLog.transition.visibilites.ua.entite',
        ])
        ->where('prestation_id', $prestation->id)
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($requete) {
            // Enrichir la réponse avec les données calculées
            $requete->sla_restant     = $this->calculerSlaRestant($requete);
            $requete->dernier_acteur  = $requete->lastLog?->triggeredBy?->name ?? 'Système';
            $requete->dernier_action  = $requete->lastLog?->comment;
            $requete->derniere_action_at = $requete->lastLog?->transitioned_at;
            return $requete;
        });
}

    /**
     * @deprecated Utiliser traiterDemande() à la place
     */
    public function storeResponse($data): Reponse
    {
        $res     = json_decode($data['responseUA']);
        $requete = Requete::findOrFail($res->requete_id);

        // Avancer le workflow selon la décision
        $decision = $res->hasPermission ? 'valider' : 'rejeter';
        $this->traiterDemande($requete->id, $decision, [
            'comment' => $res->motif ?? $res->observation ?? null,
        ]);

        // Conserver la réponse pour rétrocompatibilité
        $reponse = Reponse::firstOrNew([
            'requete_id'     => $res->requete_id,
            'unite_admin_id' => $res->unite_admin_id,
        ]);
        $reponse->fill([
            'reason'        => $res->reason      ?? null,
            'hasPermission' => $res->hasPermission ?? null,
            'observation'   => $res->observation  ?? null,
            'motif'         => $res->motif        ?? null,
        ])->save();

        return $reponse;
    }

    function getForAgenda($code)
    {
        return Agenda::with(['requete.prestation', 'requete.currentEtape', 'requete.currentStatus', 'user.agent:id,lastname,firstname'])
            ->whereHas('requete.prestation', function($q) use ($code) {
                $q->where('code', $code);
            })
            ->get();
    }

    function associateToProject($id,$projectId)  {
        $requete = Requete::findOrFail($id);
        $requete->project_id = $projectId;
        $requete->save();

        return $requete;
    }

    public function search($term)
    {
        return Requete::where(function ($q) use ($term) {
                $q->where('code', 'like', '%'.$term.'%')
                  ->orWhere('email', 'like', '%'.$term.'%')
                  ->orWhere('phone', 'like', '%'.$term.'%');
            })
            ->get();
    }
}