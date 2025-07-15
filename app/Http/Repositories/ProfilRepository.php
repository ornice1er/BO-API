<?php

namespace App\Http\Repositories;

use App\Models\Acteur;
use App\Models\Profil;
use App\Traits\Repository;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Exceptions\JsonResponseException;

class ProfilRepository
{
    use Repository;

    /**
     * The model being queried.
     *
     * @var User
     */
    protected $model;

 
     public function getAll()
    {
        $roles=Role::whereNot("name",'Super Admin')->with("permissions")->get();
        $permissions=Permission::all();
        return compact('roles', 'permissions');
    }



}