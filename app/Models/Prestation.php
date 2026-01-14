<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;
class Prestation extends Model
{

    use Filterable;
    
    protected $guarded = [];


    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class,'unite_admin_id');
    }
    public function signer2()
    {
        return $this->belongsTo(UniteAdmin::class,'signer');
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


      public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }
}
