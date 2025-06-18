<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgrementMedecin extends Model
{
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(AgrementMedecinFile::class,'am_id');
    }
}
