<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    protected $guarded = [];

    public function prestations()
    {
        return $this->hasMany(Prestation::class);
    }
}
