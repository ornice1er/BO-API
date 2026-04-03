<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeDocument extends Model
{
    use HasFactory;

    protected $table = 'etape_documents';

    protected $guarded = [];

    protected $casts = [
        'accepted_mime_types' => 'array',
        'is_required'         => 'boolean',
    ];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }

    public function etape()
    {
        return $this->belongsTo(Etape::class);
    }
}
