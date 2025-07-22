<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prestation extends Model
{

    use Filterable;
    
    protected $guarded = [];


    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class,'unite_admin_id');
    }

    public function users()
    {
        return $this->hasMany(UserPrestation::class);
    }

    public function startPoints()
    {
        return $this->hasMany(StartPoint::class,'prestation_id');
    }
    public function startPoint2()
    {
        return $this->belongsTo(UniteAdmin::class,'start_point');
    }

}
