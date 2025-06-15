<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use Filterable,HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['code', 'name', 'is_active'];

    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->code = (string) Core::generateIncrementUniqueCode('countries', 3, 'code', null);
        });
    }
}
