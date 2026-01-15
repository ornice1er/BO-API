<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ProjectRepository;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\AddRequestsToProjectRequest;
use App\Jobs\CloseProjectRequests;
use App\Models\Project;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

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
            $term = $request->term;
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
            $project = $this->projectRepository->get($id);

            if ($project->isClosed()) {
                return Common::error('Le projet est déjà clôturé', []);
            }

            CloseProjectRequests::dispatch($id);

            $this->ls->trace(['action_name' => $message, 'description' => 'Clôture du projet ' . $id . ' initiée']);

            return Common::success(
                'Clôture du projet initiée. Les requêtes seront clôturées aussi',
                ['project_id' => $id, 'status' => 'closing']
            );
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}