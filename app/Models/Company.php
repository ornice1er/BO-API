<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [];

    public function estabs()
    {
        return $this->hasMany(Establishment::class,'company_id');

    }
}
