<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapePrestationStatus extends Model
{
    use HasFactory;

    protected $table = 'etape_prestation_statuses';

    protected $guarded = [];

    public function etape()
    {
        return $this->belongsTo(Etape::class, 'etape_id');
    }

    public function ps()
    {
        return $this->belongsTo(PrestationStatus::class, 'ps_id');
    }
}
