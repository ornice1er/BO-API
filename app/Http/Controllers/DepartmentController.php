<?php

namespace App\Http\Controllers;

use App\Http\Repositories\DepartmentRepository;
use App\Http\Requests\Department\ImportDepartmentRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DepartmentController
{
    /**
     * The department being queried.
     *
     * @var Department
     */
    protected $departmentRepository;

    protected $ls;

    public function __construct(DepartmentRepository $departmentRepository, LogService $ls)
    {
        $this->departmentRepository = $departmentRepository;
        $this->ls = $ls;

    }

    /** @OA\Get(
     *      path="/departments",
     *      operationId="Department list",
     *      tags={"Department"},
     *     security={{"JWT":{}}},
     *      summary="Return Department data ",
     *      description="Get all departments",
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
     *         @OA\JsonContent(ref="#/components/schemas/Department"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Department")
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
        $message = 'Recupération de la liste des départements';
        try {
            $result = $this->departmentRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Recupération de la liste des départements', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/departments/{id}",
     *      operationId="Department show",
     *      tags={"Department"},
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
     *      summary="Return one Department data ",
     *      description="Get  Department by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Department"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Department")
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

        $message = 'Récupécuration de département ';

        try {

            $result = $this->departmentRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('département trouvé', $result);
        } catch (\Throwable $th) {
            // return $th->status();
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Post(
     *      path="/departments",
     *      operationId="Department store",
     *      tags={"Department"},
     *     security={{"JWT":{}}},
     *      summary="store Department data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Department"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Department")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/DepartmentCreate")
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
    public function store(StoreDepartmentRequest $request)
    {
        $message = 'Enregistrement d\un départment';

        try {

            $result = $this->departmentRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Départment créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Put(
     *      path="/departments/{id}",
     *      operationId="Department update",
     *      tags={"Department"},
     *     security={{"JWT":{}}},
     *      summary="update one Department data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/Department"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Department")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/DepartmentCreate")
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
    public function update(UpdateDepartmentRequest $request, $id)
    {
        $message = 'Mise à jour d\'un département ';

        try {
            $result = $this->departmentRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un département effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Delete(
     *      path="/departments/{id}",
     *      operationId="Department Delete",
     *      tags={"Department"},
     *     security={{"JWT":{}}},
     *      summary="delete Department data ",
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
        $message = 'Suppression d\'un département';

        try {
            $recup = $this->departmentRepository->get($id);
            $result = $this->departmentRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Département supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }

    }

    /** @OA\Get(
     *      path="/departments/{id}/state/{state}",
     *      operationId="Department change state",
     *      tags={"Department"},
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
     *      summary="Apply new status for one Department ",
     *      description="Get  Department by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Department"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Department")
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
        $message = 'Status d\'un département';

        try {
            $result = $this->departmentRepository->setStatus($id, $state);

            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Département $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/departments-search",
     *      operationId="Department searching",
     *      tags={"Department"},
     *     security={{"JWT":{}}},
     *      summary="return list of Department respecting term ",
     *      description="Get all filtered departments using  term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Department"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Department")
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
            $result = $this->departmentRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Flitrage éffectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($th->getMessage())]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     *     path="/departments-format",
     *     operationId="DepartmentFormat",
     *     tags={"Department"},
     *     security={{"JWT":{}}},
     *     summary="Return format file",
     *     description="Télécharger le format d'importation des départements",
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
        $message = 'Télécharger le format d\'import des départements';

        try {
            $name = "Fichier d'importation Excel";
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle(substr($name, 0, 31));
            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->setCellValue('A1', 'Liste de départements à importer');

            $sheet->getColumnDimension('A')->setWidth(30);
            $sheet->setCellValue('A2', 'Départements');

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
     *     path="/departments-import",
     *     operationId="DepartmentImport",
     *     tags={"Department"},
     *     security={{"JWT":{}}},
     *     summary="Importation de fichier",
     *     description="Importation des départements depuis un fichier Excel",
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
    public function import(ImportDepartmentRequest $request)
    {

        $message = 'Importation des départements';

        try {

            $file = $request->file('excel_file');

            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach (array_slice($rows, 2) as $row) {
                $result = $this->departmentRepository->makeStore(['nom' => $row[0]]);
            }

            return Common::success('Importation effectuée avec succès', null);

        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
