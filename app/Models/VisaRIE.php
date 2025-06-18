<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisaRIE extends Model
{
    protected $guarded = [];
    public function files()
    {
        return $this->hasMany(VisaRIEFile::class,'visa_rie_id');
    }
}
