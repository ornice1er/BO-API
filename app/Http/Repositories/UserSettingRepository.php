<?php

namespace App\Http\Repositories;

use App\Models\UserSetting;
use App\Traits\Repository;

class UserSettingRepository
{
    use Repository;

    /**
     * Le modèle UserSetting en cours de requête.
     *
     * @var UserSetting
     */
    protected $model;

    /**
     * Constructeur
     */
    public function __construct()
    {
        // Initialisation du modèle UserSetting
        $this->model = app(UserSetting::class);
    }

    /**
     * Vérifie si le UserSetting existe
     */
    public function ifExist($id)
    {
        return $this->find($id);
    }

    /**
     * Récupère tous les UserSettings avec filtrage, pagination et tri
     */
    public function getAll($request)
    {
        $per_page = 10;

        $req = UserSetting::filter(array_filter($request->all(), function ($k) {
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
     * Met à jour un UserSetting existant
     */
    public function makeUpdate($data): UserSetting
    {
        // Chercher le modèle UserSetting basé sur user_id
        $model = UserSetting::where('user_id', $data['user_id'])->first();

        if (! $model) {
            $model = new UserSetting;
            $model->user_id = $data['user_id'];
        }

        $model->use_2FA = $data['use_2FA'] ?? $model->use_2FA;
        $model->accept_notification = $data['accept_notification'] ?? $model->accept_notification;
        $model->notification_list = $data['notification_list'] ?? $model->notification_list;
        $model->mode_2FA = $data['mode_2FA'] ?? $model->mode_2FA;

        $model->save();

        return $model;
    }
}
