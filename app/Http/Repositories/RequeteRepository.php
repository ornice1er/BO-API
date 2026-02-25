<?php

namespace App\Http\Repositories;

use App\Models\Requete;
use App\Traits\Repository;
use App\Models\Prestation;
use App\Models\UniteAdmin;
use App\Models\Reponse;
use Auth;

class RequeteRepository
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
     * Check if requete exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all requetes with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Requete::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with(['affectations.copUp.agent', 'affectations.copDown.agent', 'affectation.copUp.agent', 'affectation.copDown.agent'])
            ->orderByDesc('created_at');


        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }


    /**
     * Get a specific requete by id
     */
    public function get($id)
    {
        return $this->findOrFail($id)->load(['affectations.copUp.agent', 'affectations.copDown.agent', 'affectation.copUp.agent', 'affectation.copDown.agent']);
    }



    /**
     * Store a new requete
     */
  public function makeStore(array $data): Requete
{


    // Création de l'utilisateur
    $requete = Requete::create($data);

    return $requete;
}


    /**
     * Update an existing requete
     */
  public function makeUpdate($id, array $data): Requete
{
    $model = Requete::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a requete
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest requetes
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
     * Search for requetes by name, email, or code
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


       public function getByPrestationAll($data)
        {
        $prestation=Prestation::where("code",$data['code'])->first();
        $requetes=Requete::with(['reponses.uniteAdmin','parcours','affectation'])->where('prestation_id',$prestation->id)->get();

        return $requetes;

        }


        public function getByPrestation($data)
        {       
            $prestation=Prestation::where("code",$data['code'])->first();
            $idStructure=Auth::user()->agent?->uniteAdmin?->id;
            $requetes=Requete::with(['reponses.uniteAdmin','parcours','affectation'])
                            ->where('prestation_id',$prestation->id)->where('isTreated',false)
                            ->where('isDeclined',false)
                            ->whereHas('affectations', function($q) use($idStructure) {
                                $q->where('unite_admin_down',"=", $idStructure)->where('isLast',"=", true);
                                })->get();

            return $requetes;
            
        }



            
    public function getOne($data)
    {
        $requete=Requete::with(['reponses.uniteAdmin','parcours','affectation','reponses','files'])->where('code',$data['code'])->first();
       return $requete;

    }
    public function getByPrestationTreated($data)
    {
        $code = $data['code'];

        $prestation = Prestation::where("code", $code)->first();

        $idStructure = Auth::user()->agent->uniteAdmin->id;
        $idSigner = UniteAdmin::find($prestation->signer)?->id;

       $requetes = Requete::with(['reponses.uniteAdmin','parcours','affectation','reponses','files'])
                    ->where('prestation_id', $prestation->id)
                    ->where('status', 1)
                    ->whereHas('affectations', function ($q) use ($idStructure) {
                        $q->where('unite_admin_down', $idStructure)
                        ->where('isLast', true);
                    })->get();

                    return  $requetes;

    }

  public function storeResponse($data)
{
    $res = json_decode($data['responseUA']);

    if (!$res || !isset($res->requete_id, $res->unite_admin_id)) {
        return response()->json(['message' => 'Données invalides.'], 400);
    }

    // Recherche d'une réponse existante
    $reponse = Reponse::firstOrNew([
        'requete_id' => $res->requete_id,
        'unite_admin_id' => $res->unite_admin_id,
    ]);

    // Upload du fichier s'il existe
    if (isset($data['file']) && $data['file'] instanceof \Illuminate\Http\UploadedFile) {
        $filename = FileStorage::setFile('doc_response', $data['file'], '', time());
        $reponse->note = $filename;
    }

    // Mise à jour des autres champs
    $reponse->reason         = $res->reason ?? null;
    $reponse->hasPermission  = $res->hasPermission ?? null;
    $reponse->observation    = $res->observation ?? null;
    $reponse->motif          = $res->motif ?? null;
    $reponse->preview_file   = "https://mataccueil-api.mtfp-ctd.bj/storage/preview_file.pdf"; 

    $reponse->save();

    // Mise à jour du statut de la requête
    $requete = Requete::find($res->requete_id);
    if ($requete) {
        $requete->status = 1;
        $requete->filename = "https://mataccueil-api.mtfp-ctd.bj/storage/preview_file.pdf";
        $requete->save();
    }

    // Retour de la réponse enregistrée
    return Reponse::where('requete_id', $res->requete_id)
                  ->where('unite_admin_id', $res->unite_admin_id)
                  ->first();
}

}
