<?php

namespace App\Http\Repositories;

use App\Models\Log;
use App\Traits\Repository;

class LogRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var User
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Don't forget to update the model's name
        $this->model = app(Log::class);
    }

    /**
     * Get all users with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Log::ignoreRequest(['per_page'])
            ->filter(array_filter($request->all(), function ($k) {
                return $k != 'page';
            }, ARRAY_FILTER_USE_KEY))
            ->with('user')
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Store a new user
     */
    public function makeStore($data)
    {

        $model = new Log($data);
        $model->save();
    }
}
