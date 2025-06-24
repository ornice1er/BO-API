<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestation extends Model
{
    protected $guarded = [];


    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class,'unite_admin_id');
    }

        public function users()
{
    return $this->hasMany(UserPrestation::class);
}

}
