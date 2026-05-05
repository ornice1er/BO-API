<?php
namespace App\Http\Controllers;

use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use App\Models\Requete;
use App\Models\User;
use App\Models\UniteAdmin;
use App\Models\Prestation;
use App\Models\EtapeVisibilite;
use App\Models\WorkflowTransition;
use Auth;

class DashboardController extends Controller
{
    protected $ls;

    public function __construct(LogService $ls)
    {
        $this->ls = $ls;
    }

    /**
     * Vue globale (index) : stats par prestation pour l'agent connecté.
     * Conservé pour compatibilité — utilise désormais le scope EtapeVisibilite.
     */
    function index()
    {
        $user         = Auth::user();
        $u_prestations = $user->userPrestations;
        $data         = [];
        $pieData      = [];
        $lineData     = [];

        if ($u_prestations?->count() !== 0) {
            foreach ($u_prestations as $i => $up) {
                $prestationId = $up->prestation->id;
                $etapeIds     = $this->getVisibleEtapeIds($prestationId, $user);

                $base = Requete::where('prestation_id', $prestationId)
                    ->whereIn('current_etape_id', $etapeIds);

                $data[$i]['name']      = $up->prestation->name;
                $data[$i]['pending']   = (clone $base)
                    ->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', ['en_saisie', 'en_attente']))
                    ->count();
                $data[$i]['all']       = (clone $base)->count();
                $data[$i]['treated']   = $data[$i]['all'] - $data[$i]['pending'];
                $data[$i]['delivered'] = (clone $base)
                    ->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', ['cloture', 'valide', 'prevalide', 'repertoire_edite']))
                    ->count();

                $pieData[$i] = (clone $base)
                    ->whereBetween('created_at', [now()->subDays(30), now()])
                    ->count();

                for ($j = 1; $j <= 12; $j++) {
                    $start        = date_create(date("Y-{$j}-01 00:00:00"));
                    $end          = date_create(date("Y-{$j}-t 23:59:59"));
                    $lineData[$i][] = (clone $base)
                        ->whereBetween('created_at', [$start, $end])
                        ->count();
                }
            }
        }

        return Common::success('Récupération des statistiques', [
            'data'     => $data,
            'pieData'  => $pieData,
            'lineData' => $lineData,
        ]);
    }

    /**
     * Stats détaillées pour une prestation ou le profil admin.
     */
    public function show($id)
    {
        $role         = Auth::user()->roles()->first()->name;
        $stats_by_month = [];
        $months         = [];
        $data           = [];

        switch ($role) {

            case 'Admin national':
                $data['users']      = User::count();
                $data['prestations'] = Prestation::count();
                break;

            case 'Admin Sectoriel':
                $data['users']      = User::where('entite_admin_id', Auth::user()->entite_admin_id)->count();
                $data['prestations'] = Prestation::where('entite_admin_id', Auth::user()->entite_admin_id)->count();
                $data['ua']         = UniteAdmin::where('entite_admin_id', Auth::user()->entite_admin_id)->count();
                break;

            case 'Directeur':
                $prestation = Prestation::where('code', $id)->firstOrFail();
                $user       = Auth::user();
                $etapeIds   = $this->getVisibleEtapeIds($prestation->id, $user);

                $base = Requete::where('prestation_id', $prestation->id)
                    ->whereIn('current_etape_id', $etapeIds);

                $data['total']   = (clone $base)->count();
                $data['pending'] = (clone $base)
                    ->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', ['en_saisie', 'en_attente']))
                    ->count();
                break;

            default:
                // Agent — stats complètes par prestation
                $prestation = Prestation::where('code', $id)->firstOrFail();
                $user       = Auth::user();
                $etapeIds   = $this->getVisibleEtapeIds($prestation->id, $user);

                $base = Requete::where('prestation_id', $prestation->id)
                    ->whereIn('current_etape_id', $etapeIds);

                $nouveau  = ['en_saisie', 'en_attente'];
                $rejet    = ['rejete', 'rejete_clos'];
                $valide   = ['valide', 'prevalide', 'repertoire_edite'];
                $accorde  = ['paraphe', 'en_attente_signature'];
                $terminal = ['cloture', 'rejete_clos'];
                $enCours  = array_merge($rejet, $valide, $accorde, $terminal, $nouveau);

                $data['total']     = (clone $base)->count();
                $data['news']      = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $nouveau))->count();
                $data['treated']   = $data['total'] - $data['news'];
                $data['pending']   = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereNotIn('short_name', $enCours))->count();
                $data['rejected']  = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $rejet))->count();
                $data['validated'] = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $valide))->count();
                $data['signed']    = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $accorde))->count();
                $data['finished']  = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $terminal))->count();
                $data['leaved']    = (clone $base)->where('isDeclined', true)->count();

                $current_month = (int) date('m');
                for ($i = 1; $i <= $current_month; $i++) {
                    $date_start       = date_create(date("Y-{$i}-01 00:00:00"));
                    $date_end         = date_create(date("Y-{$i}-t 23:59:59"));
                    $stats_by_month[] = (clone $base)->whereBetween('created_at', [$date_start, $date_end])->count();
                    $months[]         = $this->getMonth($i - 1);
                }
                break;
        }

        return response()->json([
            'data' => [
                'stats'          => $data,
                'stats_by_month' => $stats_by_month,
                'months'         => $months,
            ]
        ], 200);
    }

    /**
     * Compteurs rapides pour le menu (badge de nouvelles demandes).
     */
    public function statsForMenu()
    {
        $user          = Auth::user();
        $u_prestations = $user->userPrestations;
        $data          = [];

        foreach ($u_prestations as $i => $up) {
            $prestationId = $up->prestation->id;
            $etapeIds     = $this->getVisibleEtapeIds($prestationId, $user);

            $data[$i]['code'] = $up->prestation->code;
            $data[$i]['new']  = Requete::where('prestation_id', $prestationId)
                ->whereIn('current_etape_id', $etapeIds)
                ->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', ['en_saisie', 'en_attente']))
                ->count();
        }

        return response()->json(['data' => ['stats' => $data]], 200);
    }

    public function update(Request $request, $id)
    {
        $prestation = Prestation::where('code', $id)->firstOrFail();
        $user       = Auth::user();
        $etapeIds   = $this->getVisibleEtapeIds($prestation->id, $user);

        $base = Requete::where('prestation_id', $prestation->id)
            ->whereIn('current_etape_id', $etapeIds)
            ->whereBetween('created_at', [
                date_create($request->date_start),
                date_create($request->date_end),
            ]);

        $nouveau  = ['en_saisie', 'en_attente'];
        $terminal = ['cloture', 'rejete_clos'];

        $data['total']     = (clone $base)->count();
        $data['news']      = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $nouveau))->count();
        $data['treated']   = $data['total'] - $data['news'];
        $data['finished']  = (clone $base)->whereHas('currentStatus', fn($q) => $q->whereIn('short_name', $terminal))->count();

        return response()->json(['data' => $data], 200);
    }

    // ── Helpers ─────────────────────────────────────────────────

    /**
     * Retourne les etape_to_id accessibles à cet utilisateur
     * pour une prestation donnée, en respectant les visibilités du workflow.
     */
    private function getVisibleEtapeIds(int $prestationId, $user): \Illuminate\Support\Collection
    {
        $userRoles        = $user->getRoleNames();
        $userUniteAdminId = $user->agent?->unite_admin_id;

        $transitionIds = EtapeVisibilite::whereIn('role_name', $userRoles)
            ->where(function ($q) use ($userUniteAdminId) {
                $q->where('scope_type', 'requete')
                  ->orWhere(function ($q2) use ($userUniteAdminId) {
                      $q2->where('scope_type', 'unite_admin')
                         ->where('unite_admin_id', $userUniteAdminId);
                  });
            })
            ->pluck('workflow_transition_id');

        return WorkflowTransition::whereIn('id', $transitionIds)
            ->where('prestation_id', $prestationId)
            ->pluck('etape_to_id')
            ->unique();
    }

    private function getMonth(int $index): string
    {
        return [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ][$index];
    }
}
