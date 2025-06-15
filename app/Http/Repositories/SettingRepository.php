<?php

namespace App\Http\Repositories;

use App\Models\Setting;
use App\Traits\Repository;

class SettingRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var Setting
     */
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Initialisation du modèle Setting
        $this->model = app(Setting::class);
    }

    /**
     * Check if setting exists
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Get all settings with filtering, pagination, and sorting
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = Setting::filter(array_filter($request->all(), function ($k) {
            return $k != 'page';
        }, ARRAY_FILTER_USE_KEY))
            ->orderByDesc('created_at');

        if (array_key_exists('per_page', $request->all())) {
            $per_page = $request['per_page'];

            return $req->paginate($per_page);
        } else {
            return $req->get();
        }
    }

    /**
     * Update an existing setting
     */
    public function makeUpdate($data): Setting
    {
        $model = Setting::where('key', $data['key'])->first();

        $model->update(['value' => $data['value']]);

        return $model;
    }
}
