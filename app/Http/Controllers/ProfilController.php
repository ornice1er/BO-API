<?php 
namespace App\Http\Controllers;
use DB;
use App\Http\Requests;
use App\Models\Profil;
use App\Utilities\Common;
use App\Models\User;
use App\Services\LogService;
use Illuminate\Http\Request;
use App\Helpers\Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Helpers\Factory\ParamsFactory;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Http\Repositories\ProfilRepository;
use App\Http\Requests\Profil\ProfileFileRequest;
use App\Http\Requests\Profil\ProfileRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Artisan;


class ProfilController extends Controller 
{



	/**

     * Create a new controller instance.

     *

     * @return void

     */

	 protected $profilRepository;

    protected $ls;

    public function __construct(ProfilRepository $profilRepository, LogService $ls)
    {
		$this->middleware('jwt.auth', ['except' => ['DownloaFile']]);
        $this->profilRepository = $profilRepository;
        $this->ls = $ls;

        //  $this->middleware('permission:LISTER PROFIL', ['only' => ['index']]);
        //     $this->middleware('permission:CREER PROFIL', ['only' => ['store']]);
        //     $this->middleware('permission:RECUPERER PROFIL', ['only' => ['show']]);
        //     $this->middleware('permission:MODIFIER PROFIL', ['only' => ['update']]);
        //     $this->middleware('permission:SUPPRIMER PROFIL', ['only' => ['destroy']]);

    }






	/**

 * Display a listing of the resource.

 *

 * @return Response

 */

  public function index()

  {

	$message = 'Récupération de la liste des profil';
	try {
		$result = $this->profilRepository->getAll();
		$this->ls->trace(['action_name' => $message, 'description' =>json_encode($result)]);;
		return Common::success('Profil récupéré avec succès', $result);

	} catch (\Throwable $th) {
		Log::error('Error fetching Profil: ' . $th->getMessage());
		return Common::error('Une erreur est survenue lors du traitement de votre requête. Veuillez contacter l\'administrateur.', []);
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

        foreach ($request->permissions as  $value) {
            $check = DB::select('select * from role_has_permissions where permission_id='.$value.' and role_id='.$request->id);
            if ($check==null) {
                DB::table('role_has_permissions')->insert([
                    "permission_id"=>$value,
                    "role_id"=>$request->id
                ]);
            }
        }

        Artisan::call('permission:cache-reset');
        return response()->json([],200);

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
        $role=Role::find($id);
        $role->revokePermissionTo(Permission::where('id',$request->id)->first());
        return response()->json([],200);

    }




}