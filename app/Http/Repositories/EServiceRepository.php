<?php

namespace App\Http\Repositories;
use App\Exceptions\JsonResponseException;
use App\Models\Requete;
use App\Traits\Repository;
use App\Models\Parcours;
use App\Models\Prestation;
use App\Models\UniteAdmin;
use ZipArchive;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Agenda;
use App\Services\PNSService;

use App\Models\RequeteFile;
use App\Models\PlanningSlot;
use App\Models\DocumentActe;
use App\Models\EtapeDocumentProduit;
use App\Models\Municipality;
use App\Models\Department;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use DB;


class EServiceRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Requete
     */
    protected $model;

    public $departmentRepository;


    /**
     * Constructor
     */
    public function __construct(DepartmentRepository $departmentRepository)
    {
        // Don't forget to update the model's name
        $this->model = app(Requete::class);
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * Check if eservice exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all eservices with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Requete::ignoreRequest(['per_page'])
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


     public function getData($request)
    {
       switch ($request->get('type')) {
        case 'departement':
            return $this->departmentRepository->getAll($request);
             break;
            break;
        
        default:
            # code...
            break;
       }
    }

      public function getSessionData($request)
    {
        $query = Project::where('status', '!=', 'closed');

        if ($request->has('prestation_codes')) {
            $codes = array_map('trim', explode(',', $request->get('prestation_codes')));

            $query->where(function($q) use ($codes) {
                foreach ($codes as $code) {
                    $q->orWhereJsonContains('prestations', $code);
                }
            });
        }

    $check = $query
        ->whereDate('date_start', '<=', Carbon::today())
        ->whereDate('date_end',   '>=', Carbon::today())
        ->orderByDesc('date_start')
        ->first();

    if ($check) {
        return $check;
    } else {
          throw new JsonResponseException([
                'message' => 'Aucune session active trouvée',
                'success' => false,
                'data' => null,
                'warning' => '',
            ], 500);
    }
    
    }



    
    /**
     * Get a specific eservice by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new eservice
     */
    public function makeStore(array $data)
    {
        try {
            DB::beginTransaction();

            $req        = Requete::where('code', $data['meta']['code'])->first();
            $prestation = Prestation::where('code', $data['meta']['prestation_code'])->first();
            $isNew      = !$req;
            info($data['meta']);

            // ── Création ──────────────────────────────────────────────────────
            if ($isNew) {
                $req = new Requete();
                $req->prestation_id    = $prestation->id;
                $req->code             = $data['meta']['code'];
                $req->email            = $data['meta']['info']['email'];
                $req->phone            = $data['meta']['info']['phone'];
                $req->step_contents    = $data['steps'];
                $req->status           = 0;
                $req->header           = $this->getHeaders();
                $req->eps_id           = $prestation?->eps_id;
                $req->request_type      = $data['meta']['requestType'] ?? null;
                $req->planning_slot_id  = $data['meta']['planning_slot_id'] ?? null;
                $req->methode_paiement  = $data['meta']['methode_paiement'] ?? null;

                $sessionId = $data['meta']['session_id'] ?? null;
                if ($sessionId) {
                    $req->project_id = Project::find($sessionId)?->id;
                }
                $req->save();

            // ── Modification (resoumission après correction) ──────────────────
            } else {
                // Supprimer les anciens fichiers physiques et leurs enregistrements
                foreach (RequeteFile::where('requete_id', $req->id)->get() as $f) {
                    Storage::disk('public')->delete($f->file_path);
                }
                RequeteFile::where('requete_id', $req->id)->delete();
                Storage::disk('public')->deleteDirectory($req->code);

                $req->step_contents    = $data['steps'];
                $req->header           = $this->getHeaders();
                $req->request_type     = $data['meta']['requestType'] ?? null;
                $req->planning_slot_id = $data['meta']['planning_slot_id'] ?? null;
                $req->methode_paiement = $data['meta']['methode_paiement'] ?? null;
                $req->save();
            }

            // ── Extraction zip et enregistrement des fichiers ─────────────────
            $code        = $req->code;
            $host        = parse_url($data['files'], PHP_URL_HOST);
            $zipUrl      = ($host === 'localhost') ? env('APP_ZIP_URL') : $data['files'];
            $tempZipPath = storage_path("app/tmp_{$code}.zip");

            file_put_contents($tempZipPath, file_get_contents($zipUrl));
            Storage::disk('public')->makeDirectory($code);

            $zip = new ZipArchive;
            if ($zip->open($tempZipPath) === TRUE) {
                $zip->extractTo(storage_path("app/public/{$code}"));
                $zip->close();
                foreach (Storage::disk('public')->files($code) as $filePath) {
                    $fullPath = storage_path("app/public/{$filePath}");
                    RequeteFile::create([
                        'file_path'  => $filePath,
                        'file_type'  => pathinfo($fullPath, PATHINFO_EXTENSION),
                        'name'       => pathinfo($fullPath, PATHINFO_BASENAME),
                        'url'        => Storage::disk('public')->url($filePath),
                        'is_valid'   => true,
                        'requete_id' => $req->id,
                    ]);
                }
                unlink($tempZipPath);
            } else {
                throw new JsonResponseException([
                    'message' => "Impossible d'ouvrir le fichier zip",
                    'success' => false,
                    'data'    => null,
                    'warning' => null,
                ], 500);
            }

            // ── Champs géographiques ──────────────────────────────────────────
            $municipalityId = $data['meta']['municipality_id'] ?? null;
            $departmentId   = $data['meta']['department_id']   ?? null;

            if (!$municipalityId && !empty($data['meta']['Commune'])) {
                $municipalityId = Municipality::where('code', $data['meta']['Commune'])->value('id');
            }
            if (!$municipalityId && !empty($data['meta']['commune_code'])) {
                $municipalityId = Municipality::where('code', $data['meta']['commune_code'])->value('id');
            }
            if (!$departmentId && !empty($data['meta']['Departement'])) {
                $departmentId = Department::where('code', $data['meta']['Departement'])->value('id');
            }
            if (!$departmentId && !empty($data['meta']['departement_code'])) {
                $departmentId = Department::where('code', $data['meta']['departement_code'])->value('id');
            }
            if ($municipalityId) {
                $req->municipality_id = $municipalityId;
                $req->department_id   = null;
            } elseif ($departmentId) {
                $req->department_id = $departmentId;
            }
            $req->save();

            // ── Workflow & routage (création uniquement) ──────────────────────
            if ($isNew) {
                $premiereTransition = \App\Models\WorkflowTransition::where('prestation_id', $prestation->id)
                    ->where('condition_type', 'auto')
                    ->orderBy('order')
                    ->first();

                if ($premiereTransition) {
                    $req->current_etape_id  = $premiereTransition->etape_to_id;
                    $req->current_status_id = $premiereTransition->status_result_id;
                    $req->etape_started_at  = now();
                    $req->save();

                    \App\Models\RequeteEtapeLog::firstOrCreate(
                        [
                            'requete_id'             => $req->id,
                            'workflow_transition_id' => $premiereTransition->id,
                        ],
                        [
                            'etape_from_id'     => $premiereTransition->etape_from_id,
                            'etape_to_id'       => $premiereTransition->etape_to_id,
                            'status_id'         => $premiereTransition->status_result_id,
                            'triggered_by'      => null,
                            'triggered_by_type' => 'système',
                            'comment'           => 'Soumission initiale de la demande',
                            'transitioned_at'   => now(),
                            'created_at'        => now(),
                        ]
                    );
                }

                $unite_admin_down = $this->resolveUniteAdminDown($prestation, $municipalityId, $departmentId);
                if (!$req->municipality_id && !$req->department_id) {
                    $req->municipality_id = $unite_admin_down->municipality_id;
                    $req->department_id   = $unite_admin_down->department_id;
                    $req->save();
                }

                Parcours::create([
                    'libelle'    => "Soumission de la demande : " . $prestation->name,
                    'requete_id' => $req->id,
                ]);

            // ── Workflow (resoumission correction) ────────────────────────────
            } else {
                $transitionRetour = \App\Models\WorkflowTransition::where('prestation_id', $prestation->id)
                    ->where('etape_from_id', $req->current_etape_id)
                    ->where('condition_type', 'retour_correction')
                    ->where('is_active', true)
                    ->first();

                if ($transitionRetour) {
                    $req->current_etape_id  = $transitionRetour->etape_to_id;
                    $req->current_status_id = $transitionRetour->status_result_id;
                    $req->etape_started_at  = now();
                    $req->save();

                    $alreadyLogged = \App\Models\RequeteEtapeLog::where('requete_id', $req->id)
                        ->where('workflow_transition_id', $transitionRetour->id)
                        ->where('transitioned_at', '>=', now()->subMinutes(5))
                        ->exists();

                    if (!$alreadyLogged) {
                        \App\Models\RequeteEtapeLog::create([
                            'requete_id'             => $req->id,
                            'workflow_transition_id' => $transitionRetour->id,
                            'etape_from_id'          => $transitionRetour->etape_from_id,
                            'etape_to_id'            => $transitionRetour->etape_to_id,
                            'status_id'              => $transitionRetour->status_result_id,
                            'triggered_by'           => null,
                            'triggered_by_type'      => 'système',
                            'comment'                => 'Resoumission après correction du citoyen',
                            'transitioned_at'        => now(),
                            'created_at'             => now(),
                        ]);
                    }
                }

                Parcours::create([
                    'libelle'    => "Resoumission de la demande après correction",
                    'requete_id' => $req->id,
                ]);
            }

            DB::commit();
            return true;

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Résout l'unité admin de traitement (CS ou DDEMP) selon le périmètre géographique.
     * Priorité : commune > département > start_point par défaut.
     */
    private function resolveUniteAdminDown(Prestation $prestation, ?int $municipalityId, ?int $departmentId): UniteAdmin
    {
        // 1. Si une commune est fournie, chercher la CS correspondante
        if ($municipalityId) {
            $cs = UniteAdmin::where('municipality_id', $municipalityId)
                ->whereHas('typeUniteAdmin', fn($q) => $q->where('libelle', 'Service'))
                ->first();

            if ($cs) return $cs;
        }

        // 2. Si un département est fourni, chercher la DDEMP correspondante
        if ($departmentId) {
            $ddemp = UniteAdmin::where('department_id', $departmentId)
                ->whereNull('municipality_id')
                ->whereHas('typeUniteAdmin', fn($q) => $q->where('libelle', 'Direction Départementale'))
                ->first();

            if ($ddemp) return $ddemp;
        }

        // 3. Défaut : start_point configuré sur la prestation
        return UniteAdmin::findOrFail($prestation->startPoint2?->id ?? $prestation->unite_admin_id);
    }

private function getHeaders()
{
    $request = request();
    $h_datas = [];

    if ($request->hasHeader('uxp-service')) {
        $h_datas['uxp-service']    = $request->header('uxp-service');
        $h_datas['uxp-client']     = $request->header('uxp-client');
        $h_datas['application-id'] = $request->header('application-id');
        $h_datas['response-token'] = $request->header('response-token');
    }

    return $h_datas;
}





    /**
     * Update an existing eservice
     */
  public function makeUpdate($id, array $data): Requete
{
    $model = Requete::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a eservice
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest eservices
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
     * Search for eservices by name, email, or code
     */
    public function search($term)
    {
        return Requete::where(function ($q) use ($term) {
                $q->where('code', 'like', '%'.$term.'%')
                  ->orWhere('email', 'like', '%'.$term.'%')
                  ->orWhere('phone', 'like', '%'.$term.'%');
            })
            ->get();
    }

    public function setRDV($data) {

            $agenda= new Agenda();
            $agenda->date_start=$data['date_start'];
            $agenda->date_end=$data['date_end'];
            $agenda->title=$data['title'];
            $agenda->status=$data['status'];
            $agenda->description=$data['description'];
            $agenda->priority=$data['priority'];
            $agenda->requete_id=Prestation::whereCode($data['code'])->first()?->id;
            $agenda->save();

        return $agenda;
    }

    public function getRDVSlots($request)
    {
        $query = PlanningSlot::available()
            ->with(['uniteAdmin:id,libelle', 'prestation:id,name,code']);

        if ($request->filled('prestation_code')) {
            $prestation = Prestation::whereCode($request->prestation_code)->first();
            if ($prestation) {
                $query->where(function ($q) use ($prestation) {
                    $q->where('prestation_id', $prestation->id)
                      ->orWhereNull('prestation_id');
                });
            }
        }

        if ($request->filled('prestation_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('prestation_id', $request->prestation_id)
                  ->orWhereNull('prestation_id');
            });
        }

        if ($request->filled('unite_admin_id')) {
            $query->where('unite_admin_id', $request->unite_admin_id);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('slot_date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('slot_date', '<=', $request->date_end);
        }

        if ($request->filled('session_type')) {
            $query->where('session_type', $request->session_type);
        }

        return $query->orderBy('slot_date')->orderBy('heure_debut')->get();
    }

    public function recupDoc(array $data): array
    {
        DB::beginTransaction();

        try {
            // 1. Résoudre la requête — la prestation en est déduite directement
            $requete = Requete::with('prestation')->where('code', $data['code_demande'])->firstOrFail();

            // 2. Trouver le document produit configuré pour l'étape courante
            $query = EtapeDocumentProduit::where('prestation_id', $requete->prestation_id)
                ->where('etape_edition_id', $requete->current_etape_id);

            // Filtrage optionnel par type si renseigné
            if (!empty($data['type'])) {
                $query->where('type', $data['type']);
            }

            $docProduit = $query->first();

            if (!$docProduit) {
                \Log::error('EServiceRepository: aucun document produit trouvé', [
                    'requete_id'       => $requete->id,
                    'prestation_id'    => $requete->prestation_id,
                    'current_etape_id' => $requete->current_etape_id,
                    'type'             => $data['type'] ?? null,
                ]);
                throw new JsonResponseException([
                    'message' => "Aucun document produit configuré pour l'étape courante de cette demande",
                    'success' => false,
                    'data'    => null,
                ], 422);
            }

            // 3. Trouver ou initialiser le DocumentActe
            $acte = DocumentActe::firstOrNew([
                'requete_id'     => $requete->id,
                'doc_produit_id' => $docProduit->id,
            ]);

            if (!$acte->numero_identification) {
                $prefix  = $docProduit->numero_prefix ?? 'DOC';
                $annee   = now()->year;
                $dernier = DocumentActe::where('doc_produit_id', $docProduit->id)
                    ->whereYear('created_at', $annee)->count();
                $acte->numero_identification = sprintf('%s-%d-%04d', $prefix, $annee, $dernier + 1);
                $acte->generated_at = now();
            }

            $host = parse_url($data['url'], PHP_URL_HOST);
            $docUrl = ($host === 'localhost')
            ? env('APP_DOC_FAKE_URL')
            : $data['url']; 

            // 4. Télécharger le fichier depuis l'URL externe
            $response = Http::timeout(30)->get($docUrl);

            if (!$response->successful()) {
                throw new JsonResponseException([
                    'message' => "Impossible de télécharger le document depuis l'URL fournie (HTTP {$response->status()})",
                    'success' => false,
                    'data'    => null,
                ], 502);
            }

           

            // Déduire l'extension depuis le Content-Type ou l'URL
            $contentType = $response->header('Content-Type') ?? 'application/pdf';
            $ext = match(true) {
                str_contains($contentType, 'pdf')  => 'pdf',
                str_contains($contentType, 'word') => 'docx',
                str_contains($contentType, 'png')  => 'png',
                str_contains($contentType, 'jpeg') => 'jpg',
                default                            => pathinfo(parse_url($docUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'pdf',
            };

            $filename = $acte->numero_identification . '_ext_' . time() . '.' . $ext;
            $dir      = 'documents/' . $requete->code;
            $path     = $dir . '/' . $filename;

            Storage::disk('public')->put($path, $response->body());

            $acte->file_path    = $path;
            $acte->file_url     = Storage::disk('public')->url($path);
            $acte->status       = 'en_edition';
            $acte->save();

            // 5. Avancer le workflow uniquement si l'option est activée sur le doc produit
            if ($docProduit->avancer_workflow) {
                $conditionType = $docProduit->condition_type ?? 'validation';
                app(RequeteRepository::class)->avancerWorkflow($requete, $conditionType, [
                    'comment'  => 'Document reçu depuis générateur externe',
                    'metadata' => [
                        'doc_acte_id'  => $acte->id,
                        'file_path'    => $path,
                        'source_url'   => $docUrl,
                        'type'         => $data['type'] ?? null,
                        'source'       => 'generateur_externe',
                    ],
                ]);
            }

            DB::commit();

            return [
                'acte'     => $acte->load(['docProduit', 'requete']),
                'file_url' => $acte->file_url,
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function closeRequest($data) {
        $req=Requete::where("code",$data['code'])->first();
        $req->status=2;
        $req->save();

        $pnsService = new PNSService($req->header,[
            "data" => null,
            "message" => "Clôture de la demande : ".$req->code,
            "status" => true,
            "decision" => $data['decision'] ?? null,
            "link" => null,
        ]);
        return $data;
    }

}
