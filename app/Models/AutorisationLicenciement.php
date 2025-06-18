<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorisationLicenciement extends Model
{
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(AutorisationLicenciementFile::class,'al_id');
    }
}
