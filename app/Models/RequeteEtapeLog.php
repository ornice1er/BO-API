<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequeteEtapeLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'metadata'         => 'array',
        'transitioned_at'  => 'datetime',
    ];

    public function requete()
    {
        return $this->belongsTo(Requete::class, 'requete_id');
    }

    public function transition()
    {
        return $this->belongsTo(WorkflowTransition::class, 'workflow_transition_id');
    }

    public function etapeFrom()
    {
        return $this->belongsTo(Etape::class, 'etape_from_id');
    }

    public function etapeTo()
    {
        return $this->belongsTo(Etape::class, 'etape_to_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
