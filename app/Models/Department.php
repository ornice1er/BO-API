<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use Filterable,HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['code', 'name', 'country_id', 'is_active'];

    protected $dates = ['deleted_at'];

    // Relation avec le modèle Country
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->code = (string) Core::generateIncrementUniqueCode('departments', 3, 'code', "D");
        });
    }

    public function municipalities()
    {

        return $this->hasMany(Municipality::class, 'department_id');
    }
}
