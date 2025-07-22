<?php

namespace App\Http\Repositories;

use App\Models\Reponse;
use App\Traits\Repository;
use App\Services\PNSService;

class ReponseRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Reponse
     */
    protected $model;


    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Reponse::class);
    }

    /**
     * Check if reponse exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all reponses with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Reponse::ignoreRequest(['per_page'])
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
     * Get a specific reponse by id
     */
    public function get($id)
    {
        return $this->findOrFail($id);
    }



    /**
     * Store a new reponse
     */
  public function makeStore(array $data): Reponse
{


    // Création de l'utilisateur
    $reponse = Reponse::create($data);

    return $reponse;
}


    /**
     * Update an existing reponse
     */
  public function makeUpdate($id, array $data): Reponse
{
    $model = Reponse::findOrFail($id);



    // Mise à jour des données utilisateur
    $model->update($data);


    return $model;
}


    /**
     * Delete a reponse
     */
    public function makeDestroy($id)
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get the latest reponses
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
     * Search for reponses by name, email, or code
     */
    public function search($term)
    {
        $query = Reponse::query(); // Start with an empty query
        $attrs = ['lib_couvert']; // Attributes you want to search in

        foreach ($attrs as $value) {
            $query->orWhere($value, 'like', '%'.$term.'%');
        }

        return $query->get(); // Return the search results
    }







        public function needCorrection(Request $request)
    {
        $req=Requete::whereId($request->requete_id)->first();
        $header=(array)json_decode($req->header);
        $ps= new PNSService($header,['officialComment' => $request->commentaire,'decision' => "updaterequest"]);
        $response=$ps->reply();
 

        if ($response->status()>=200 && $response->status()<300) {
            $req->update(["needCorrection"=>true,"comment"=>$request->commentaire]);
            return true;
        }else {
            return false;

        }
    }
    public function reachedAgreement(Request $request)
    {
        $req=Requete::whereId($request->requete_id)->first();
        $header=(array)json_decode($req->header);
        $ps= new PNSService($header,['decision' => "agreement_reached"]);
        $response=$ps->reply();

        if ($response->status()>=200 && $response->status()<300) {
            $req->update(["hasReachedAgreement"=>true]);
            return true;
        }else {
            return false;

        }
    }

    public function decline(Request $request){

        $req=Requete::whereId($request->requete_id)->first();
        $header=(array)json_decode($req->header);
         $ps= new PNSService($header,['officialComment' =>  $request->commentaire,'decision' => "rejected"]);
        $response=$ps->reply();


        if ($response->status()>=200 && $response->status()<300) {
            $req->update(["isDeclined"=>true,"comment"=>$request->commentaire]);
            return true;

        }else {
            return false;
        }

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $req=Requete::whereId($request->requete_id)->first();
        $header=(array)json_decode($req->header);
        $content=$req->content;

        $ps= new PNSService($header,['content' =>  $content,'decision' => "accepted"]);
        $response=$ps->reply();


        if ($response->status()>="200" &&  $response->status()<"300") {
            $req->update([
                'isFinished' => true,
                'isTreated'=>true
            ]);
            return true;

        }else {
            return false;

        }
    }

        public function storeContent(Request $request)
    {

            $id=$request->currentDescId;
           switch ($id) {
               case '0':
                $req=Requete::find($request->id)->update(['content'=>$request->content]);
                break;
               case '1':
                $req=Requete::find($request->id)->update(['content2'=>$request->content]);
                break;
               case '2':
                $req=Requete::find($request->id)->update(['content3'=>$request->content]);
                break;
               
               default:
               $req=Requete::find($request->id)->update(['content'=>$request->content]);
               break;
           }
        $req=Requete::whereId($request->id)->first();
     
            $header=(array)json_decode($req->header);
            $content=$request->content;

        $ps= new PNSService($header,['content' =>  $content,'created_at' =>  $req?->created_at,'decision' => "preview"]);
        $response=$ps->reply();

            if ($response->status()>="200" &&  $response->status()<"300") {
                  return true;
      
              }else {
                  return false;
      
              }
        return true;

    }
}
