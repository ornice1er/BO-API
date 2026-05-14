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

    $session    = $sessionLabels[$agenda->session_type] ?? $agenda->session_type;
    $rdvType    = $rdvLabels[$agenda->rdv_type]         ?? $agenda->rdv_type;
    $dateStart  = \Carbon\Carbon::parse($agenda->date_start)->format('d/m/Y à H:i');
    $dateEnd    = $agenda->date_end
        ? \Carbon\Carbon::parse($agenda->date_end)->format('d/m/Y à H:i')
        : 'Non définie';
    $duree      = $agenda->duration_minutes ? $agenda->duration_minutes . ' minutes' : 'Non précisée';
    $prestation = e($agenda->requete->prestation->name ?? 'N/A');
    $auteur     = e($agenda->user?->name ?? 'Un agent du service');
    $note       = $agenda->description
        ? '<p style="margin:16px 0;color:#374151;"><strong>📝 Note :</strong> ' . e($agenda->description) . '</p>'
        : '';

    $rows = [
        ['Titre',       e($agenda->title)],
        ['Prestation',  $prestation],
        ['Type de RDV', e($rdvType)],
        ['Session',     e($session)],
        ['Date début',  e($dateStart)],
        ['Date fin',    e($dateEnd)],
        ['Durée',       e($duree)],
        ['Priorité',    e($agenda->priority)],
        ['Origine',     e($agenda->from)],
    ];

    $tableRows = '';
    foreach ($rows as [$label, $value]) {
        $tableRows .= "
            <tr>
                <td style=\"padding:10px 14px;font-weight:600;color:#6b7280;background:#f9fafb;white-space:nowrap;border-bottom:1px solid #e5e7eb;\">{$label}</td>
                <td style=\"padding:10px 14px;color:#111827;border-bottom:1px solid #e5e7eb;\">{$value}</td>
            </tr>";
    }

    return "<!DOCTYPE html>
<html lang=\"fr\">
<head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"></head>
<body style=\"margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif;\">
  <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f3f4f6;padding:32px 0;\">
    <tr><td align=\"center\">
      <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.1);\">

        <!-- En-tête -->
        <tr><td style=\"background:#1d4ed8;padding:28px 32px;\">
          <h1 style=\"margin:0;color:#ffffff;font-size:20px;\">Confirmation de programme</h1>
        </td></tr>

        <!-- Corps -->
        <tr><td style=\"padding:28px 32px;\">
          <p style=\"margin:0 0 20px;color:#374151;\">Bonjour,</p>
          <p style=\"margin:0 0 24px;color:#374151;\">Vous avez un programme planifié concernant votre dossier. Veuillez trouver ci-dessous les détails :</p>

          <h2 style=\"margin:0 0 12px;font-size:15px;color:#1d4ed8;text-transform:uppercase;letter-spacing:.05em;\">📋 Détails du programme</h2>
          <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"border:1px solid #e5e7eb;border-radius:6px;overflow:hidden;border-collapse:collapse;\">
            {$tableRows}
          </table>

          {$note}

          <p style=\"margin:24px 0 0;color:#374151;\">Merci de confirmer votre disponibilité en répondant à ce message.</p>
        </td></tr>

        <!-- Pied de page -->
        <tr><td style=\"background:#f9fafb;padding:18px 32px;border-top:1px solid #e5e7eb;\">
          <p style=\"margin:0;color:#6b7280;font-size:13px;\">Cordialement,<br><strong>{$auteur}</strong></p>
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>";
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
