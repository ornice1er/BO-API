<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;
class FonctionAgent extends Model
{
    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];


       public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            $model->code = Str::uuid();
        });

    }
}
