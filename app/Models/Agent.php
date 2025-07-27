<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;

class Agent extends Model
{
    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];

    protected $guarded = [];


    public function fonctionAgent()
    {
        return $this->belongsTo(FonctionAgent::class,'fonction_agent_id');
    }

    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class,'unite_admin_id');
    }

    public function entiteAdmin()
    {
        return $this->belongsTo(EntiteAdmin::class,'entite_admin_id');
    }

    public function user()
    {
        return $this->hasOne(User::class,'agent_id');
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

