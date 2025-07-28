<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;
class UniteAdmin extends Model
{
    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];

    public function typeUniteAdmin()
    {
        return $this->belongsTo(TypeUniteAdmin::class,'type_unite_admin_id');
    }
    public function parent()
    {
        return $this->belongsTo(UniteAdmin::class,'ua_parent_code');
    }
    public function department()
    {
        return $this->belongsTo(Department::class,'department_id');
    }
    public function agent()
    {
        return $this->hasOne(Agent::class,'unite_admin_id');
    }

        public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            $model->code = Str::uuid();
        });
    }
}
