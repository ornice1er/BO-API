<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class UserProject extends Model
{
    use Filterable,HasFactory, HasRoles,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['user_id', 'project_id', 'is_active'];

    protected $guard_name = 'api';

    protected $dates = ['deleted_at'];

    // Relation avec le modèle Country
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            // $model->code = (string) Core::generateIncrementUniqueCode('userProjects',3,'code',null);
        });
    }
}
