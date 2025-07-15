<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class UniteAdmin extends Model
{
    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];

    public function typeUniteAdmin()
    {
        return $this->belongsTo(TypeUniteAdmin::class,'type_unite_admin_id');
    }
    public function agent()
    {
        return $this->hasOne(Agent::class,'unite_admin_id');
    }
}
