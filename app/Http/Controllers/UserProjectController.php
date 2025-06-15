<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserProjectRepository;
use App\Http\Requests\UserProject\StoreUserProjectRequest;
use App\Http\Requests\UserProject\UpdateUserProjectRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UserProjectController
{
    /**
     * The userProject being queried.
     *
     * @var UserProject
     */
    protected $userProjectRepository;

    protected $ls;

    public function __construct(UserProjectRepository $userProjectRepository, LogService $ls)
    {
        $this->userProjectRepository = $userProjectRepository;
        $this->ls = $ls;

    }

    /** @OA\Get(
     *      path="/user-projects",
     *      operationId="UserProject list",
     *      tags={"UserProject"},
     *      security={{"JWT":{}}},
     *      summary="Return UserProject data",
     *      description="Get all user projects",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Can be used for filtering data by name",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProject"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserProject")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function index(Request $request)
    {
        $message = 'Récupération de la liste des assignations';

        try {
            $result = $this->userProjectRepository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Récupération de la liste des assignations.', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Get(
     *      path="/user-projects/{id}",
     *      operationId="UserProject show",
     *      tags={"UserProject"},
     *     security={{"JWT":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the user project",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      summary="Return one UserProject data",
     *      description="Get UserProject by ID",
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProject"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserProject")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function show($id)
    {
        $message = 'Récupération d\'une assignation';

        try {
            $result = $this->userProjectRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success('Assignation trouvée', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Post(
     *      path="/user-projects",
     *      operationId="UserProject store",
     *      tags={"UserProject"},
     *     security={{"JWT":{}}},
     *      summary="Store UserProject data",
     *      description="",
     *
     *      @OA\Response(
     *          response=201,
     *          description="successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProject"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserProject")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProjectCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function store(StoreUserProjectRequest $request)
    {
        $message = 'Enregistrement d\'une assignation';

        try {
            $result = $this->userProjectRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Assignation créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Put(
     *      path="/user-projects/{id}",
     *      operationId="UserProject update",
     *      tags={"UserProject"},
     *     security={{"JWT":{}}},
     *      summary="Update one UserProject data",
     *      description="",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the userProject",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProject"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/UserProject")
     *      ),
     *
     *      @OA\RequestBody(
     *          description="body request",
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/UserProjectCreate")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function update(UpdateUserProjectRequest $request, $id)
    {
        $message = 'Mise à jour d\'une assignation';

        try {
            $result = $this->userProjectRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success("Mise à jour d'une assignation effectuée avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /** @OA\Delete(
     *      path="/user-projects/{id}",
     *      operationId="UserProject Delete",
     *      tags={"UserProject"},
     *     security={{"JWT":{}}},
     *      summary="Delete UserProject data",
     *      description="",
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the userProject",
     *          required=true,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=204,
     *          description="successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/DeleteResponseData"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/DeleteResponseData")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Expired session"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Server Error"
     *      )
     * )
     */
    public function destroy($id)
    {
        $message = 'Suppression d\'une assignation';

        try {
            $recup = $this->userProjectRepository->get($id);

            $result = $this->userProjectRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Assignation supprimée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Get(
     *     path="/user-projects/{id}/state/{state}",
     *     operationId="userProjectChangeState",
     *     tags={"UserProject"},
     *     security={{"JWT":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user project",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="state",
     *         in="path",
     *         description="New state for the user project",
     *         required=true,
     *
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     summary="Apply new status for one UserProject",
     *     description="Change the state of the user project identified by ID",
     *
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserProject"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/UserProject")
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function changeState($id, $state)
    {
        $message = 'Changement de l\'état d\'une assignation';

        try {
            $result = $this->userProjectRepository->setStatus($id, $state);
            $statusMessage = $state == 1 ? 'activé' : 'désactivé';
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success("Assignation $statusMessage avec succès", $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * @OA\Post(
     *     path="/user-projects-search",
     *     operationId="UserProjectSearching",
     *     tags={"UserProject"},
     *     security={{"JWT":{}}},
     *     summary="Return list of UserProjects respecting term",
     *     description="Get all filtered userProjects using term",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/UserProject"),
     *
     *         @OA\XmlContent(ref="#/components/schemas/UserProject")
     *     ),
     *
     *     @OA\RequestBody(
     *         description="Body request",
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/TermSearch")
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=419,
     *         description="Expired session"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error"
     *     )
     * )
     */
    public function search(Request $request)
    {
        $message = 'Filtrage des assignations';

        try {
            $term = $request->term;
            $result = $this->userProjectRepository->search($term);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success('Filtrage effectué avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
