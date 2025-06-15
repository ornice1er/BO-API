<?php

namespace App\Http\Controllers;

use App\Http\Repositories\MunicipalityRepository;
use App\Http\Requests\Municipality\ImportMunicipalityRequest;
use App\Http\Requests\Municipality\StoreMunicipalityGhmRequest;
use App\Http\Requests\Municipality\StoreMunicipalityRequest;
use App\Http\Requests\Municipality\UpdateMunicipalityGhmRequest;
use App\Http\Requests\Municipality\UpdateMunicipalityRequest;
use App\Models\Municipality;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MunicipalityController extends Controller
{
    /**
     * The municipality being queried.
     *
     * @var Municipality
     */
    protected $municipalityRepository;

    protected $ls;

    public function __construct(MunicipalityRepository $municipalityRepository, LogService $ls)
    {
        $this->municipalityRepository = $municipalityRepository;
        $this->ls = $ls;

        $this->middleware('basic.auth')->except(['show']);

    }

    /** @OA\Get(
     *      path="/municipalities",
     *      operationId="Municipality list",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="Return Municipality data ",
     *      description="Get all municipalities",
     *
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Can used for filtering data by name",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *  @OA\Parameter(
     *      name="with",
     *      in="query",
     *      description="Ecrire les relations à ajouter à la récupération",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function index(Request $request)
    {
        $message = 'Recupération de la liste des communes';
        try {
            $result = $this->municipalityRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Recupération de la liste des communes', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/municipalities-ghm",
     *      operationId="Municipality GHM list",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="Return Municipality GHM data ",
     *      description="Get all municipalities",
     *
     * @OA\Parameter(
     *      name="name",
     *      in="query",
     *      description="Can used for filtering data by name",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *  @OA\Parameter(
     *      name="with",
     *      in="query",
     *      description="Ecrire les relations à ajouter à la récupération",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function indexGhm(Request $request)
    {
        $message = 'Recupération de la liste des communes';
        try {
            $result = $this->municipalityRepository->getAllGhm($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Recupération de la liste des communes', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/municipalities/{id}",
     *      operationId="Municipality show",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     * @OA\Parameter(
     *      name="with",
     *      in="query",
     *      description="Ecrire les relations à ajouter à la récupération",
     *      required=false,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Return one Municipality data ",
     *      description="Get  Municipality by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function show($id)
    {
        $message = 'Récupécuration de commune';

        try {

            $result = $this->municipalityRepository->get($id, request()->with);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Commune trouvée', $result);
        } catch (\Throwable $th) {
            // return $th->status();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/municipalities",
     *      operationId="Municipality store",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="store Municipality data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/MunicipalityCreate")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function store(StoreMunicipalityRequest $request)
    {
        $message = 'Enregistrement d\une commune';

        try {

            $result = $this->municipalityRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Commune créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/municipalities-store-ghm",
     *      operationId="Municipality GHM store ",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="store Municipality GHM data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/MunicipalityGhmCreate")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function storeGhm(StoreMunicipalityGhmRequest $request)
    {
        $message = 'Enregistrement d\une commune GHM';

        try {

            $result = $this->municipalityRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Commune créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Put(
     *      path="/municipalities/{id}",
     *      operationId="Municipality update",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="update one Municipality data ",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),

     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/MunicipalityCreate")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function update(UpdateMunicipalityRequest $request, $id)
    {
        $message = 'Mise à jour d\'une commune';

        try {
            $result = $this->municipalityRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'une commune effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Put(
     *      path="/municipalities-ghm/{id}",
     *      operationId="Municipality GHM update",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="update one Municipality GHM data ",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),

     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/MunicipalityCreate")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function updateGhm(UpdateMunicipalityGhmRequest $request, $id)
    {
        $message = 'Mise à jour d\'une commune';

        try {
            $result = $this->municipalityRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'une commune effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Delete(
     *      path="/municipalities/{id}",
     *      operationId="Municipality Delete",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="delete Municipality data ",
     *      description="",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *     @OA\Response(
     *         response=204,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/DeleteResponseData"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/DeleteResponseData")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function destroy($id)
    {
        $message = 'Suppression d\'une commune';

        try {
            $recup = $this->municipalityRepository->get($id, null);

            $result = $this->municipalityRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Commune supprimée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/municipalities/{id}/state/{state}",
     *      operationId="Municipality change state",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *
     *  @OA\Parameter(
     *      name="state",
     *      in="path",
     *      description="",
     *      required=true,
     *
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      summary="Apply new status for one Municipality ",
     *      description="Get  Municipality by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function changeState($id, $state)
    {
        $message = 'Status d\'une commune';

        try {
            $result = $this->municipalityRepository->setStatus($id, $state);

            $statusMessage = $state == 1 ? 'activée' : 'désactivée';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Commune $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/municipalities-search",
     *      operationId="Municipality searching",
     *      tags={"Municipality"},
     *     security={{"JWT":{}}},
     *      summary="return list of Municipality respecting term ",
     *      description="Get all filtered municipalities using  term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Municipality"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Municipality")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/TermSearch")
     *     ),
     *
     * @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     * @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     *)
     */
    public function search(Request $request)
    {
        $message = 'Filtrage';

        try {
            $term = $request->term;
            $result = $this->municipalityRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Flitrage éffectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     *     path="/municipalities-format",
     *     operationId="MunicipalityFormat",
     *     tags={"Municipality"},
     *     security={{"JWT":{}}},
     *     summary="Return format file",
     *     description="Télécharger le format d'importation des municipalités",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Document PDF format PDF",
     *
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *     @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *     @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *     @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function downloadFormat(Request $request)
    {
        $message = 'Télécharger le format d\'import des municipalités';

        try {
            $name = "Fichier d'importation Excel";
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(substr($name, 0, 31));
            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->setCellValue('A1', 'Liste de municipalités à importer');

            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->setCellValue('A2', 'Municipalités');

            $type = 'xlsx';
            $filename = date('d_m_Y_h_i_s_').$name.'.'.$type;
            if ($type == 'xlsx') {
                $writer = new Xlsx($spreadsheet);
            } elseif ($type == 'xls') {
                $writer = new Xls($spreadsheet);
            }

            ob_start();
            $writer->save('php://output');
            $excelContent = ob_get_clean();

            // Convertir en base64
            $base64 = base64_encode($excelContent);

            $base64 = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,'.$base64;

            return Common::success('Téléchargement du format d\'import effectué avec succès', $base64);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/municipalities-import",
     *     operationId="MunicipalityImport",
     *     tags={"Municipality"},
     *     security={{"JWT":{}}},
     *     summary="Importation de fichier",
     *     description="Importation des municipalités depuis un fichier Excel",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *
     *             @OA\Schema(
     *
     *                 @OA\Property(
     *                     property="excel_file",
     *                     type="string",
     *                     format="binary",
     *                     description="Fichier Excel à importer"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Importation réussie depuis Excel",
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Requête invalide"
     *      ),
     *     @OA\Response(
     *          response=419,
     *          description="Session expirée"
     *      ),
     *     @OA\Response(
     *          response=404,
     *          description="Ressource non trouvée"
     *      ),
     *     @OA\Response(
     *          response=500,
     *          description="Erreur serveur"
     *      )
     * )
     */
    public function import(ImportMunicipalityRequest $request)
    {

        $message = 'Importation des municipalités';

        try {

            $file = $request->file('excel_file');

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach (array_slice($rows, 2) as $row) {
                $result = $this->municipalityRepository->makeStore(['nom' => $row[0]]);
            }

            return Common::success('Importation effectuée avec succès', null);

        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
