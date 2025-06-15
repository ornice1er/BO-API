<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipality extends Model
{
    use Filterable,HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['code', 'name', 'department_id', 'is_active', 'project_id', 'edition_id'];

    protected $dates = ['deleted_at'];

    // Relation avec le modèle Municipality
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->code = (string) Core::generateIncrementUniqueCode('municipalities', 3, 'code', null);
        });
    }
}
