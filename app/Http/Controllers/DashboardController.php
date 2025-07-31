<?php
namespace App\Http\Controllers;

use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Requete;
use App\Models\User;
use App\Models\UniteAdmin;
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
            $u_prestations=Auth::user()->userPrestations;
            
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


     

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      /*  $prestation=Prestation::where("code",$id)->first();
        $idStructure=Auth::user()->agent->uniteAdmin->id;
        $query=Requete::query();
        $query->where('prestation_id',$prestation->id);
        $query->whereHas('affectations', function($q) use($idStructure) {
            $q->where('unite_admin_down',"=", $idStructure)->where('isLast',"=", true);
            });
        $data['totale']= $query->count();
        $data['treated']= $query->where('isTreated',true)->count();
        //$data['authorized']= $query->where('isAutorized',true)->count();
        $data['noTreated']= $data['totale']-$data['treated'];
        $data['finished']= $query->where('isFinished',true)->count();*/

        switch (Auth::user()->roles()->first()->name) {
            case 'Admin national':
                $data['users']= User::count();
                $data['prestations']= Prestation::count();
                $stats_by_month=[];
                $months=[];
                break;
            
            case 'Admin Sectoriel':
                $data['users']= User::where("entite_admin_id",Auth::user()->entite_admin_id)->count();
                $data['prestations']= Prestation::where("entite_admin_id",Auth::user()->entite_admin_id)->count();
                $data['ua']= UniteAdmin::where("entite_admin_id",Auth::user()->entite_admin_id)->count();
                $stats_by_month=[];
                $months=[];
                break;

                case 'Directeur':
                    $prestation=Prestation::where("code",$id)->first();
                    $query=Requete::query();
                    $idStructure=Auth::user()->agent->uniteAdmin->id;
                    $data['pending']=  Requete::where('prestation_id',$prestation->id)->where('status',0)->whereHas('affectations', function($q) use($idStructure) {
                        $q->where('unite_admin_down',"=", $idStructure)->where('isLast',"=", true);
                        })->count();

                    $stats_by_month=[];
                    $months=[];
                    break;
            
            default:
            $prestation=Prestation::where("code",$id)->first();
            $query=Requete::query();
            $query=$query->where('prestation_id',$prestation->id);
            $data['total']= Requete::where('prestation_id',$prestation->id)->count();
            $data['news']= Requete::where('prestation_id',$prestation->id)->where('status',0)->count();
            $data['pending']=  Requete::where('prestation_id',$prestation->id)->where('status',2)->count();
            $data['reached']=  Requete::where('prestation_id',$prestation->id)->where('status',3)->count();
            $data['rejected']=  Requete::where('prestation_id',$prestation->id)->where('status',4)->count();
            $data['treated']=  Requete::where('prestation_id',$prestation->id)->where('status','!=',0)->count();
            $data['validated']=  Requete::where('prestation_id',$prestation->id)->where('status',5)->count();
            $data['signed']=  Requete::where('prestation_id',$prestation->id)->where('status',7)->count();
            $data['finished']=Requete::where('prestation_id',$prestation->id)->whereIn('status',[4,7])->count();
            $data['leaved']=  Requete::where('prestation_id',$prestation->id)->where('status',8)->count();
            $data['fixed']=     Requete::where('prestation_id',$prestation->id)->where('status',9)->count();
    
           
            $current_month=date('m');
            for ($i=1; $i <= (int)$current_month; $i++) { 
                $date_start=date_create(date("Y-".$i."-01 00:00:00"));
                $date_end=date_create(date("Y-".$i."-t 23:59:59"));
                $stats_by_month[]= Requete::where('prestation_id',$prestation->id)->whereBetween('created_at',[$date_start,$date_end])->count();
                $months[]=$this->getMonth($i-1);
            }
                break;
        }
      
    
      


return response()->json([
    'data'=>[
        "stats" =>$data,
        "stats_by_month" =>$stats_by_month,
        "months" =>$months
    ]
], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $prestation=Prestation::where("code",$id)->first();
        $idStructure=Auth::user()->agent->uniteAdmin->id;
        $query=Requete::query();
        $query->whereBetween('created_at',[date_create($request->date_start),date_create($request->date_end)]);
        $query->where('prestation_id',$prestation->id);
        $query->whereHas('affectations', function($q) use($idStructure) {
            $q->where('unite_admin_down',"=", $idStructure)->where('isLast',"=", true);
            });
        
                $data['totale']= $query->count();
                $data['treated']= $query->where('isTreated',true)->count();
                //$data['authorized']= $query->where('isAutorized',true)->count();
                $data['noTreated']= $data['totale']-$data['treated'];
                $data['finished']= $query->where('isFinished',true)->count();


           
              
     
        
        return response()->json([
            'data'=>$data
        ], 200);

    }

    public function getMonth($index)
    {
        $months=[
            "Janvier",
            "Février",
            "Mars",
            "Avril",
            "Mai",
            "Juin",
            "Juillet",
            "Août",
            "Septembre",
            "Octobre",
            "Novembre",
            "Décembre"
        ];

        return $months[$index];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function statsForMenu()
    {
        $user=Auth::user();
        $u_prestations=$user->userPrestations;
        $idStructure=$user->agent->uniteAdmin->id;
        $data=array();
        $i=0;
        if($u_prestations->count() !=0){
            foreach ($u_prestations as $up) {
                $data[$i]["code"]=$up->prestation->code;
                $data[$i]['new']= Requete::where('prestation_id',$up->prestation->id)->where("status",0)->whereHas('affectations', function($q) use($idStructure) {
                    $q->where('unite_admin_down',"=", $idStructure)->where('isLast',"=", true);
                    })->count();
                $i++;
            }
        }

        return response()->json([
            'data'=>[
                "stats" =>$data,
            ]
        ], 200);

    }

}
