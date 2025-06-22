<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Village extends Model
{
    use Filterable, HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['name', 'code', 'district_id', 'is_active'];

    protected $dates = ['deleted_at'];

    public function district()
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Boot du modèle
     */
    public static function boot()
    {
        parent::boot();

        // Générer un code unique lors de la création
        self::creating(function ($model) {
            $model->code = (string) Core::generateIncrementUniqueCode('villages', 5, 'code', "V");
        });
    }
}
