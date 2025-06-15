<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use Filterable, HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['name', 'description', 'pc_id'];

    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        // Lors de la création d'un projet, génération d'un code unique
        self::creating(function ($model) {
            // Exemple pour générer un code unique pour le projet (vous pouvez ajuster la logique)
            //   $model->code = (string) Core::generateIncrementUniqueCode('projects', 3, 'code', null);
        });
    }

    public function userProjects()
    {
        return $this->hasMany(UserProject::class, 'project_id');

    }
}
