<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Agenda extends Model
{
    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];


    function requete() {
        return $this->belongsTo(Requete::class,'requete_id');
    }

    function user() {
        return $this->belongsTo(\App\Models\User::class,'user_id');
    }
}
