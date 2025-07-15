<?php
namespace App\Http\Controllers;

use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Requete;
use App\Models\Prestation;
use Auth;

class DashboardController extends Controller
{
  

    protected $ls;

    public function __construct(LogService $ls)
    {
     //   $this->companyRepository = $companyRepository;
        $this->ls = $ls;

        //$this->middleware('auth:api')->except(['getNotified', 'show']);

    }

    function index() {
           $data=array();
        $pieData=array();
        $lineData=array();


     //   if(Auth::user()->hasRole('Directeur Technique')){
            $u_prestations=Auth::user()->userprestation;
            
            $i=0;
            if($u_prestations?->count() !=0){
                foreach ($u_prestations as $up) {
                    $idStructure=Auth::user()->agent->uniteAdmin->id;
                    $data[$i]["name"]=$up->prestation->name;
                    $data[$i]["pending"]=Requete::where('isTreated',false)->where('prestation_id',$up->prestation->id)->whereHas('affectations', function($q) use($idStructure) {
                        $q->where('unite_admin_down',"=", $idStructure)->where('isLast',"=", true);
                        })->get()->count();
                    $data[$i]["all"]=Requete::where('prestation_id',$up->prestation->id)->whereHas('affectations', function($q) use($idStructure) {
                        $q->where('unite_admin_down',"=", $idStructure);
                        })->get()->count();
                    $data[$i]["treated"]= $data[$i]["all"]-$data[$i]["pending"];
                    $data[$i]["delivered"]=Requete::where('isFinished',true)->where('prestation_id',$up->prestation->id)->whereHas('affectations', function($q) use($idStructure) {
                        $q->where('unite_admin_down',"=", $idStructure);
                        })->get()->count();
                    $today=date_create(date("Y-m-d h:i:s"));
                    $date=date_create(date("Y-m-d h:i:s"))->modify("-30day");

                        $pieData[$i]=Requete::where('prestation_id',$up->prestation->id)->whereBetween('created_at',[$today,$date])->count();

                        for ($j=1; $j <= 12 ; $j++) { 
                            $start=date_create(date("Y-".$j."-01 h:i:s"));
                            $end=date_create(date("Y-".$j."-t 23:59:59"));
                            $lineData[$i][]=Requete::where('prestation_id',$up->prestation->id)->whereBetween('created_at',[$start,$end])->count();
                        }
                    $i++;


                }
            }
      //  }
   
                   return Common::success('Recupération de la liste des départements',['data'=>$data,"pieData"=>$pieData,"lineData"=>$lineData]);
    }

}
