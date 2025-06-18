<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttestationDePresenceAuPoste extends Model
{
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(AttestationDePresenceAuPosteFile::class,'adpap_id');
    }
}
