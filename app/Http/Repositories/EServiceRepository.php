<?php

namespace App\Http\Repositories;
use App\Exceptions\JsonResponseException;
use App\Models\Requete;
use App\Traits\Repository;
use App\Models\Parcours;
use App\Models\Prestation;
use App\Models\Affectation;
use App\Models\UniteAdmin;
use ZipArchive;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Agenda;
use App\Services\PNSService;

use App\Models\RequeteFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    
    $check= Project::where('status', '!=', 'closed')->first();  

    if ($check) {
        return $check;
    }else{
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

        $req=Requete::where("code",$data['meta']['code'])->first();
        $prestation=Prestation::where("code",$data['meta']['prestation_code'])->first();
     
        if (!$req) {
            $req= new Requete();
            $req->prestation_id=$prestation->id;
            $req->code=$data['meta']['code'];
            $req->email=$data['meta']['info']['email'];
            $req->phone=$data['meta']['info']['phone'];
            $req->step_contents=$data['steps'];
            // $req->lastname=$data['meta']['info']['lastname'];
            // $req->firstname=$data['meta']['info']['firstname'];
            if (in_array($data['meta']['prestation_code'],['PS00709','PS00710'])) {
              $today = Carbon::today();
            $project = Project::whereDate('date_start', '<=', $today)
            ->whereDate('date_end', '>=', $today)
            ->first();

            $req->project_id = $project?->id;
            }
            $req->status=0;
            $req->header=$this->getHeaders();
                    $req->eps_id=$prestation?->eps_id;

            $req->save();
        }else{
        $req->prestation_id=$prestation->id;
        $req->code=$data['meta']['code'];
        $req->email=$data['meta']['info']['email'];
        $req->phone=$data['meta']['info']['phone'];
        $req->step_contents=$data['steps'];
        // $req->lastname=$data['meta']['info']['lastname'];
        // $req->firstname=$data['meta']['info']['firstname'];
        $req->header=$this->getHeaders();
        $req->eps_id=$prestation?->eps_id;
        $req->save();



        }

        $code = $req->code;
        $host = parse_url($data['files'], PHP_URL_HOST);

        $zipUrl = ($host === 'localhost')
            ? env('APP_ZIP_URL')
            : $data['files']; 
        $tempZipPath = storage_path("app/tmp_{$code}.zip");
        file_put_contents($tempZipPath, file_get_contents($zipUrl));
        $extractPath = storage_path("app/public/{$code}");
        Storage::disk('public')->makeDirectory($code);
        $zip = new ZipArchive;
        if ($zip->open($tempZipPath) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();

            // 3. Parcours des fichiers extraits et enregistrement
            $files = Storage::disk('public')->files($code);
            foreach ($files as $filePath) {
                $fullPath = storage_path("app/public/{$filePath}");
                $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

                RequeteFile::create([
                    'file_path' => $filePath,
                    'file_type' => $extension,
                    'name' => pathinfo($fullPath, PATHINFO_BASENAME),
                    'url' => Storage::disk('public')->url($filePath),
                    'is_valid' => true,
                    'requete_id' => $req->id,
                ]);
            }
                // Supprimer le zip temporaire
            unlink($tempZipPath);

        } else {

             throw new JsonResponseException([
                'message' => "Impossible d'ouvrir le fichier zip",
                'success' => false,
                'data' => null,
                'warning' => null,
            ], 500);
        }
       
        $premiereTransition = \App\Models\WorkflowTransition::where('prestation_id', $prestation->id)
        ->where('condition_type', 'auto')
        ->orderBy('order')
        ->first();

    if ($premiereTransition) {
        // 2. Pointer l'étape courante et le statut
        $req->current_etape_id   = $premiereTransition->etape_to_id;
        $req->current_status_id  = $premiereTransition->status_result_id;
        $req->etape_started_at   = now();
        $req->save();

        // 3. Logger la transition dans requete_etape_logs
        \App\Models\RequeteEtapeLog::create([
            'requete_id'              => $req->id,
            'workflow_transition_id'  => $premiereTransition->id,
            'etape_from_id'           => $premiereTransition->etape_from_id,
            'etape_to_id'             => $premiereTransition->etape_to_id,
            'status_id'               => $premiereTransition->status_result_id,
            'triggered_by'            => null,      // système
            'triggered_by_type'       => 'système',
            'comment'                 => 'Soumission initiale de la demande',
            'transitioned_at'         => now(),
            'created_at'              => now(),
        ]);
    }

        Parcours::create(['libelle'=>"Soumission de la demande :".$prestation->name,'requete_id'=>$req->id]);
      //  $unite_admin_down=UniteAdmin::where('ua_parent_code',$prestation->uniteAdmin->id)->first();
        $unite_admin_down=UniteAdmin::find($prestation->startPoint2?->id);
        
        Affectation::create([
            'unite_admin_up'=>$prestation->uniteAdmin->id,
            'unite_admin_down'=>$unite_admin_down->id,
            'requete_id'=>$req->id,
            'isLast'=>1,
            'sens'=>1,
        ]);
        Parcours::create(['libelle'=>"Affectation de la demande  ".$req->code." par le/la ".$prestation->uniteAdmin->libelle." au/à la " .$unite_admin_down->libelle ,'requete_id'=>$req->id]);
         
            DB::commit();

        return true;
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }



    }

    private function getHeaders(){
        $headers=request()->header;

        $h_datas=array();

        info($h_datas);

        if (isset($headers['uxp-service'])) {
        $h_datas['uxp-service']=$headers['uxp-service'][0];
        $h_datas['uxp-client']=$headers['uxp-client'][0];
        $h_datas['application-id']=$headers['application-id'][0];
        $h_datas['response-token']=$headers['response-token'][0];

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
        $query = Requete::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
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

    public function closeRequest($data) {
        $req=Requete::where("code",$data['code'])->first();
        $req->status=2;
        $req->save();

        $pnsService = new PNSService($req->header,[
            "data" => null,
            "message" => "Clôture de la demande : ".$req->code,
            "status" => true,
            "decision" => $data['decision'] ?? null
        ]);
        return $data;
    }

}
