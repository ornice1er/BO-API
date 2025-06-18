<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutorisationDeStage extends Model
{
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(AutorisationDeStageFile::class,'ads_id');
    }
}
