<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowTransition extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'notify_requérant' => 'boolean',
        'notify_agent'     => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function etapeFrom()
    {
        return $this->belongsTo(Etape::class, 'etape_from_id');
    }

    public function etapeTo()
    {
        return $this->belongsTo(Etape::class, 'etape_to_id');
    }

    public function statusResult()
    {
        return $this->belongsTo(Status::class, 'status_result_id');
    }

    public function prestation()
    {
        return $this->belongsTo(Prestation::class, 'prestation_id');
    }

    public function notifications()
    {
        return $this->hasMany(EtapeNotification::class, 'workflow_transition_id');
    }

    public function visibilites()
    {
        return $this->hasMany(EtapeVisibilite::class, 'workflow_transition_id');
    }
public function etape()      { return $this->belongsTo(Etape::class, 'etape_to_id'); }
}
