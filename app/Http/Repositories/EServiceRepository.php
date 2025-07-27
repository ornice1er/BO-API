<?php

namespace App\Http\Repositories;

use App\Models\Requete;
use App\Traits\Repository;
use App\Models\Parcours;
use App\Models\Prestation;
use App\Models\Affectation;
use App\Models\UniteAdmin;
use ZipArchive;
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


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Requete::class);
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
            $req->status=0;
            $req->header=$this->getHeaders();
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
        $req->save();
        }

        $code = $req->code;
        $zipUrl = $data['files']; 
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
                    'url' => Storage::url($filePath),
                    'is_valid' => true,
                    'requete_id' => $req->id,
                ]);
            }
                // Supprimer le zip temporaire
            unlink($tempZipPath);

        } else {
            throw new \Exception("Impossible d'ouvrir le fichier zip");
        }

        Parcours::create(['libelle'=>"Soumission de la demande de délivrance d'attestation de non litige",'requete_id'=>$req->id]);
        
        $unite_admin_down=UniteAdmin::where('ua_parent_code',$prestation->uniteAdmin->id)->first();
        
        Affectation::create([
            'unite_admin_up'=>$prestation->uniteAdmin->id,
            'unite_admin_down'=>$unite_admin_down->id,
            'requete_id'=>$req->id,
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
}
