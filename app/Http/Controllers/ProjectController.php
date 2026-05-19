<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProjectRepository;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\AddRequestsToProjectRequest;
use App\Jobs\CloseProjectRequests;
use App\Models\Prestation;
use App\Services\PNSService;
use App\Models\Project;
use App\Models\Requete;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\{Alignment, Border, Color, Fill, Font};
use Storage;


class ProjectController extends Controller
{
    /**
     * The project being queried.
     *
     * @var Project
     */
    protected $projectRepository;

    protected $ls;

    public function __construct(ProjectRepository $projectRepository, LogService $ls)
    {
        $this->projectRepository = $projectRepository;
        $this->ls = $ls;
    }

    /** @OA\Get(
     *      path="/projects",
     *      operationId="Project list",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="Return Project data ",
     *      description="Get all projects",
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
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Project"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Project")
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
        $message = 'Récupération de la liste des projets';

        try {
            $result = $this->projectRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/projects/{id}",
     *      operationId="Project show",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
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
     *      summary="Return one Project data with requests ",
     *      description="Get project by ID with associated requests",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Project"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Project")
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
        $message = 'Récupération d\'un projet avec ses requêtes';

        try {
            $result = $this->projectRepository->getWithRequests($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Projet trouvé', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/projects",
     *      operationId="Project store",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="store Project data ",
     *      description="",
     *
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Project"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Project")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProjectCreate")
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
    public function store(StoreProjectRequest $request)
    {
        $message = 'Enregistrement d\'un projet';

        try {
            $result = $this->projectRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Projet créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/projects/{id}",
     *      operationId="Project update",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="update one Project data ",
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
     *         @OA\JsonContent(ref="#/components/schemas/Project"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Project")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/ProjectCreate")
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
    public function update(UpdateProjectRequest $request, $id)
    {
        $message = 'Mise à jour d\'un projet';

        try {
            $result = $this->projectRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Mise à jour d\'un projet effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/projects/{id}",
     *      operationId="Project Delete",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="delete Project data ",
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
        $message = 'Suppression d\'un projet';

        try {
            $recup = $this->projectRepository->get($id);

            $result = $this->projectRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Projet supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/projects-search",
     *      operationId="Project searching",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="Return list of Project respecting term",
     *      description="Get all filtered projects using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Project"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Project")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
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
        $message = 'Filtrage des projets';

        try {
            $term = $request->input('search') ?? $request->input('term');
            $result = $this->projectRepository->search($term);

            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/projects/{id}/state/{state}",
     *      operationId="Project change state",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
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
     *      summary="Apply new status for one Project ",
     *      description="Get  project by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Project"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/Project")
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
        $message = 'Changement de l\'état d\'un projet';

        try {
            $result = $this->projectRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Projet $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Add requests to a project
     * 
     * @OA\Post(
     *      path="/projects/{id}/add-requests",
     *      operationId="addRequestsToProject",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="Add requests to a project",
     *      description="Add multiple request IDs to a specific project",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Project ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\RequestBody(
     *         description="Request IDs array",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"request_ids"},
     *             @OA\Property(
     *                 property="request_ids",
     *                 type="array",
     *                 items=@OA\Items(type="integer"),
     *                 example={1, 2, 3, 4}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Requests added successfully"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function addRequests(AddRequestsToProjectRequest $request, $id)
    {
        $message = 'Ajout des requêtes à un projet';

        try {
            $project = $this->projectRepository->addRequests($id, $request->request_ids);
            $this->ls->trace(['action_name' => $message, 'description' => 'Requêtes ajoutées au projet ' . $id]);

            return Common::success('Requêtes ajoutées au projet avec succès', $project);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Close a project and dispatch job to close all requests
     * 
     * @OA\Post(
     *      path="/projects/{id}/close",
     *      operationId="closeProject",
     *      tags={"Project"},
     *      security={{"JWT":{}}},
     *      summary="Close a project",
     *      description="Close a project and dispatch a job to close all associated requests in background",
     *
     *   @OA\Parameter(
     *      name="id",
     *      in="path",
     *      description="Project ID",
     *      required=true,
     *      @OA\Schema(type="integer")
     *   ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Project closure initiated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request (project already closed)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function closeProject($id)
    {
        $message = 'Clôture d\'un projet';

        try {
            $project = Project::findOrFail($id);

            if ($project->isClosed()) {
                return Common::error('Le projet est déjà clôturé', []);
            }

            if (!$project->closing_filename) {
                return Common::error('Fichier de clôture manquant', []);
            }

            if (env('PROJECT_CLOSE_ASYNC', false)) {
                CloseProjectRequests::dispatch($id);

                $this->ls->trace(['action_name' => $message, 'description' => 'Clôture async initiée — projet ' . $id]);

                return Common::success('Clôture initiée en arrière-plan', ['project_id' => $id, 'status' => 'closing']);
            }

            $this->executeClose($project);

            $this->ls->trace(['action_name' => $message, 'description' => 'Projet ' . $id . ' clôturé']);

            return Common::success('Projet clôturé avec succès', $project->fresh());

        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    private function executeClose(Project $project): void
    {
        $closingFileUrl = Storage::disk('public')->url($project->closing_filename);

        $prestationCodes = is_array($project->prestations)
            ? $project->prestations
            : json_decode($project->prestations, true) ?? [];

        $prestations = Prestation::whereIn('code', $prestationCodes)
            ->where('is_group_delivered', true)
            ->get();

        foreach ($prestations as $prestation) {
            $requetes = Requete::where('project_id', $project->id)
                ->where('prestation_id', $prestation->id)
                ->get();

            $uniqueToken = encrypt([
                'project_id'    => $project->id,
                'prestation_id' => $prestation->id,
                'expires_at'    => now()->addDays(30)->toDateTimeString(),
            ]);

            $uniqueLink = route('project.closing.file', ['token' => $uniqueToken]);

            foreach ($requetes as $requete) {
                try {
                    $pnsService = new PNSService($requete->header, [
                        'data'     => null,
                        'message'  => "Publication d'arrêté de clôture demande : " . $requete->code,
                        'status'   => true,
                        'link'     => $uniqueLink,
                        'decision' => $prestation->decision,
                    ]);

                    $requete->filename = $uniqueLink;
                    $requete->save();

                    $pnsService->reply();

                } catch (\Exception $e) {
                    \Log::error("Clôture projet {$project->id} — erreur PNS requête {$requete->code} : " . $e->getMessage());
                }
            }
        }

        $project->update(['status' => 'closed']);
    }

    function exportList(Request $request) {
           try {


// Récupérer les données
$requetes = Requete::with(['prestation', 'currentStatus', 'currentEtape'])
    ->whereIn('id', $request->ids)
    ->get(['id', 'code', 'email', 'phone', 'prestation_id', 'created_at']);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Liste des demandes');

// ──────────────────────────────────────────
// LIGNE 1 : Titre principal
// ──────────────────────────────────────────
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'Liste des demandes');
$sheet->getStyle('A1')->applyFromArray([
    'font' => [
        'bold'  => true,
        'size'  => 16,
        'color' => ['argb' => 'FFFFFFFF'],
        'name'  => 'Arial',
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF1F3864'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
]);
$sheet->getRowDimension(1)->setRowHeight(40);

// ──────────────────────────────────────────
// LIGNE 2 : Sous-titre avec date de génération
// ──────────────────────────────────────────
$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A2', 'Généré le ' . now()->format('d/m/Y à H:i'));
$sheet->getStyle('A2')->applyFromArray([
    'font' => [
        'italic' => true,
        'size'   => 10,
        'color'  => ['argb' => 'FF808080'],
        'name'   => 'Arial',
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FFD9E1F2'],
    ],
]);
$sheet->getRowDimension(2)->setRowHeight(20);

// ──────────────────────────────────────────
// LIGNE 3 : Ligne vide de séparation
// ──────────────────────────────────────────
$sheet->getRowDimension(3)->setRowHeight(8);

// ──────────────────────────────────────────
// LIGNE 4 : En-têtes des colonnes
// ──────────────────────────────────────────
$headers = ['Code Prestation', 'Prestation', 'Code Demande', 'Email', 'Téléphone', 'Étape', 'Statut', 'Date dépôt'];
$headerColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

foreach ($headers as $i => $header) {
    $cell = $headerColumns[$i] . '4';
    $sheet->setCellValue($cell, $header);
}

$sheet->getStyle('A4:H4')->applyFromArray([
    'font' => [
        'bold'  => true,
        'size'  => 11,
        'color' => ['argb' => 'FFFFFFFF'],
        'name'  => 'Arial',
    ],
    'fill' => [
        'fillType'   => Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF2E75B6'], // bleu moyen
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['argb' => 'FFBFBFBF'],
        ],
    ],
]);
$sheet->getRowDimension(4)->setRowHeight(25);

// ──────────────────────────────────────────
// LIGNES DE DONNÉES : alternance de couleurs
// ──────────────────────────────────────────
$rowIndex = 5;
foreach ($requetes as $req) {
    $sheet->setCellValue("A{$rowIndex}", $req->prestation?->code ?? '');
    $sheet->setCellValue("B{$rowIndex}", $req->prestation?->name ?? '');
    $sheet->setCellValue("C{$rowIndex}", $req->code);
    $sheet->setCellValue("D{$rowIndex}", $req->email);
    $sheet->setCellValue("E{$rowIndex}", $req->phone);
    $sheet->setCellValue("F{$rowIndex}", $req->currentEtape?->name ?? '—');
    $sheet->setCellValue("G{$rowIndex}", $req->currentStatus?->name ?? '—');
    $sheet->setCellValue("H{$rowIndex}", $req->created_at?->format('d/m/Y') ?? '');

    // Alternance blanc / bleu très clair
    $bgColor = ($rowIndex % 2 === 0) ? 'FFDCE6F1' : 'FFFFFFFF';

    $sheet->getStyle("A{$rowIndex}:H{$rowIndex}")->applyFromArray([
        'font' => ['name' => 'Arial', 'size' => 10],
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['argb' => $bgColor],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FFBFBFBF'],
            ],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);
    $sheet->getRowDimension($rowIndex)->setRowHeight(20);

    $rowIndex++;
}

// ──────────────────────────────────────────
// LARGEUR DES COLONNES
// ──────────────────────────────────────────
$sheet->getColumnDimension('A')->setWidth(18);
$sheet->getColumnDimension('B')->setWidth(35);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(35);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(25);
$sheet->getColumnDimension('G')->setWidth(20);
$sheet->getColumnDimension('H')->setWidth(15);

// ──────────────────────────────────────────
// SAUVEGARDE
// ──────────────────────────────────────────
$filename = uniqid() . '.xlsx';
$path = Storage::disk('public')->path($filename);

$writer = new Xlsx($spreadsheet);
$writer->save($path);
            return Common::success("Fichier excel",Storage::disk('public')->url($filename));

               } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => "Erreur", 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }

    }

    public function accessClosingFile($token)
{
    try {
        $data = decrypt($token);

        if (now()->gt($data['expires_at'])) {
            abort(403, 'Lien expiré');
        }

        $project = Project::findOrFail($data['project_id']);

        return Storage::disk('public')->download($project->closing_filename);

    } catch (\Exception $e) {
        abort(403, 'Lien invalide');
    }
}
}