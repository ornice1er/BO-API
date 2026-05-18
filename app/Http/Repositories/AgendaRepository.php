<?php

namespace App\Http\Repositories;

use App\Models\Agenda;
use App\Traits\Repository;
use App\Services\PNSService;
use App\Models\Requete;
use App\Exceptions\JsonResponseException;
use Auth, Log;
class AgendaRepository
{
    use Repository;

    protected $requeteRepository;

    /**
     * The model being queried.
     *
     * @var Agenda
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct(RequeteRepository $requeteRepository)
    {
        $this->requeteRepository = $requeteRepository;
        // Don't forget to update the model's namep
        $this->model = app(Agenda::class);
    }

    /**
     * Check if agenda exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all agendas with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Agenda::with(['user.agent:id,lastname,firstname'])
            ->ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    /**
     * Get a specific agenda by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new agenda
     */
  public function makeStore(array $data): Agenda
{


    if (Auth::check()) {
    $data['user_id'] = Auth::user()->id;
    }
    $data['status'] = $data['status'] ?? 'Ouvert';
    $requete = Requete::findOrFail($data['requete_id']);
    $agenda = Agenda::create($data);

    // if ($requete) {
    //     $pnsService = new PnsService($requete->header,[
    //         'success' => 'true',
    //         'decision' => 'program',
    //         'message' => 'Agenda créé avec succès',
    //         'data' =>  $this->getContent($agenda)
    //     ]);
    // }
   

    return $agenda;
}

function sendMail($id) {

    $agenda = Agenda::with(['planningSlot.uniteAdmin'])->findOrFail($id);
    $requete = $agenda->requete;
    if ($requete) {
        $decisionMap = [
            'premier_rdv'      => '1rdv',
            'second_rdv'       => '2rdv',
            'suivi_traitement' => 'program',
        ];
        $decision = $decisionMap[$agenda->rdv_type] ?? 'program';

        $pnsService = new PnsService($requete->header,[
            'success' => 'true',
            'decision' => $decision,
            'message' => 'Agenda créé avec succès',
            'data' =>  $this->getContent($agenda)
        ]);
        $result= $pnsService->reply();

        $this->requeteRepository->avancerWorkflow($requete, 'validation', [
            'comment' => 'Prise de rdv par ' . Auth::user()?->name,
        ]);

        if ($result===false) {
            throw new JsonResponseException([
                'message' => 'Echec d\'envoyé.Le service PNS a répondu avec une erreur. Mais le flux a été avancé.',
                'success' => false,
                'data' => null,
                'warning' => null
            ], 500);
        }
        }
    return $agenda;
}

function getContent($agenda): string
{
    $sessionLabels = [
        'matinee'         => 'Matinée',
        'apres_midi'      => 'Après-midi',
        'journee_entiere' => 'Journée entière',
    ];

    $rdvLabels = [
        'premier_rdv'      => 'Premier RDV',
        'second_rdv'       => 'Second RDV',
        'suivi_traitement' => 'Suivi traitement',
    ];

    $session    = $sessionLabels[$agenda->session_type]  ?? $agenda->session_type;
    $rdvType    = $rdvLabels[$agenda->rdv_type]          ?? $agenda->rdv_type;
    $dateStart  = \Carbon\Carbon::parse($agenda->date_start)->format('d/m/Y à H:i');
    $dateEnd    = $agenda->date_end
        ? \Carbon\Carbon::parse($agenda->date_end)->format('d/m/Y à H:i')
        : 'Non définie';
    $duree      = $agenda->duration_minutes ? $agenda->duration_minutes . ' min' : 'Non précisée';
    $prestation = $agenda->requete->prestation->name ?? 'N/A';
    $auteur     = $agenda->user?->name ?? 'Un agent du service';

    $lines = [
        "Bonjour,",
        "",
        "Vous avez un programme planifié concernant votre dossier.",
        "",
        "--- DÉTAILS DU PROGRAMME ---",
        "",
        "Titre       : {$agenda->title}",
        "Prestation  : {$prestation}",
        "Type de RDV : {$rdvType}",
        "Session     : {$session}",
        "Date début  : {$dateStart}",
        "Date fin    : {$dateEnd}",
        "Durée       : {$duree}",
    ];

    if ($agenda->planningSlot) {
        $slot = $agenda->planningSlot;
        $lines[] = "Lieu        : " . ($slot->uniteAdmin->libelle ?? 'N/A');
        $lines[] = "Créneau     : " . \Carbon\Carbon::parse($slot->slot_date)->format('d/m/Y')
                   . ' à ' . substr($slot->heure_debut, 0, 5);
    }

    if ($agenda->description) {
        $lines[] = "";
        $lines[] = "Note : {$agenda->description}";
    }

    $lines = array_merge($lines, [
        "",
        "Merci de confirmer votre disponibilité en répondant à ce message.",
        "",
        "Cordialement,",
        $auteur,
    ]);

    return implode("\n", $lines);
}
    /**
     * Update an existing agenda
     */
  public function makeUpdate($id, array $data): Agenda
{
    $model = Agenda::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a agenda
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest agendas
     */
    public function getLatest()
    {
        return $this->latest()->get();
    }

    public function setStatus($id, $status)
    {
        return $this->findOrFail($id)->update(['status' => $status]);
    }

    /**
     * Search for agendas by name, email, or code
     */
    public function search($term)
    {
        return Agenda::where(function ($q) use ($term) {
                $q->where('title', 'like', '%'.$term.'%')
                  ->orWhere('description', 'like', '%'.$term.'%');
            })
            ->get();
    }
}
