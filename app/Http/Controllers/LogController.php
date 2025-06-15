<?php

namespace App\Http\Controllers;

use App\Http\Repositories\logRepository;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LogController
{
    /**
     * The Log repository being queried.
     *
     * @var LogRepository
     */
    protected $logRepository;

    public function __construct(logRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /** @OA\Get(
     *      path="/logs",
     *      operationId="Log list",
     *      tags={"Log"},
     *      summary="Return Log data",
     *      description="Get all log",
     *
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Can be used for filtering data by user id",
     *          required=false,
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Log"),
     *
     *          @OA\XmlContent(ref="#/components/schemas/Log")
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
        try {
            $result = $this->logRepository->getAll($request);

            return Common::success('Journal des logs', $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }
}
