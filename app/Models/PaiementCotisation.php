<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementCotisation extends Model
{    protected $guarded = [];


    public function files()
    {
        return $this->hasMany(PaiementCotisationFile::class,'pc_id');
    }
}
