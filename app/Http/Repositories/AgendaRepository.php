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
        // Don't forget to update the model's name
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

        $req = Agenda::ignoreRequest(['per_page'])
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
    $requete = Requete::findOrFail($data['requete_id']);
    // Création de l'utilisateur
    $agenda = Agenda::create($data);

    if ($requete) {
        $pnsService = new PnsService($requete->header,[
            'success' => 'true',
            'decision' => 'program',
            'message' => 'Agenda créé avec succès',
            'data' =>  $this->getContent($agenda)
        ]);
    }
   

    return $agenda;
}

function sendMail($id) {

    
    $agenda = Agenda::findOrFail($id);
    $requete = $agenda->requete;
    if ($requete) {
        $pnsService = new PnsService($requete->header,[
            'success' => 'true',
            'decision' => 'program',
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
}

function getContent($agenda) {
    $sessionLabels = [
        'matinee'        => 'Matinée',
        'apres_midi'     => 'Après-midi',
        'journee_entiere'=> 'Journée entière',
    ];

    $rdvLabels = [
        'premier_rdv'      => 'Premier RDV',
        'second_rdv'       => 'Second RDV',
        'suivi_traitement' => 'Suivi traitement',
    ];

    $session = $sessionLabels[$agenda->session_type] ?? $agenda->session_type;
    $rdvType = $rdvLabels[$agenda->rdv_type]         ?? $agenda->rdv_type;

    $dateStart = \Carbon\Carbon::parse($agenda->date_start)->format('d/m/Y à H:i');
    $dateEnd   = $agenda->date_end
        ? \Carbon\Carbon::parse($agenda->date_end)->format('d/m/Y à H:i')
        : 'Non définie';

    $prestation = $agenda->requete->prestation->name ?? 'N/A';
    $auteur     = $agenda->user?->name ?? 'Un agent du service';

    $content = "
        Bonjour,

        Vous avez un programme planifié concernant votre dossier. Veuillez trouver ci-dessous les détails :

        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        📋 DÉTAILS DU PROGRAMME
        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        • Titre         : {$agenda->title}
        • Prestation    : {$prestation}
        • Type de RDV   : {$rdvType}
        • Session       : {$session}
        • Date début    : {$dateStart}
        • Date fin      : {$dateEnd}
        • Durée         : " . ($agenda->duration_minutes ? $agenda->duration_minutes . ' minutes' : 'Non précisée') . "
        • Priorité      : {$agenda->priority}
        • Origine       : {$agenda->from}

        ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        " . ($agenda->description ? "📝 Note : {$agenda->description}" : '') . "

        Merci de confirmer votre disponibilité en répondant à ce message.

        Cordialement,
        {$auteur}
    ";

    return trim($content);
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
        return $this->findOrFail($id)->update(['is_active' => $status]);
    }

    /**
     * Search for agendas by name, email, or code
     */
    public function search($term)
    {
        $query = Agenda::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }
}
