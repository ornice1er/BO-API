<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisaCA extends Model
{
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(VisaCAFile::class,'visa_ca_id');
    }
}
