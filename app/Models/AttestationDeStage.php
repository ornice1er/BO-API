<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationDeStage extends Model
{
    protected $guarded = [];

    public function files()
    {
        return $this->hasMany(AttestationDeStageFile::class,'as_id');
    }
}
