<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeNotification extends Model
{
     protected $table = 'etape_notifications';
    protected $guarded = [];
 
    protected $casts = [
        'extra_data' => 'array',
    ];
 
    public function workflowTransition()
    {
        return $this->belongsTo(WorkflowTransition::class, 'workflow_transition_id');
    }
}
