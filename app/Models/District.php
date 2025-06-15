<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use Filterable,HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['code', 'name', 'municipality_id', 'is_active'];

    protected $dates = ['deleted_at'];

    // Relation avec le modèle District
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->code = (string) Core::generateIncrementUniqueCode('districts', 3, 'code', null);
        });
    }
}
