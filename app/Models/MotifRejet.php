<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotifRejet extends Model
{
    use HasFactory;

    protected $table = 'motifs_rejet';

    protected $guarded = [];

    protected $casts = [
        'allow_complement' => 'boolean',
        'is_final'         => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class, 'prestation_id');
    }

    public function etape()
    {
        return $this->belongsTo(Etape::class, 'etape_id');
    }
}
