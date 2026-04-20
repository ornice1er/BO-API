<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeVisibilite extends Model
{
    use HasFactory;

    protected $table = 'etape_visibilites';

    protected $guarded = [];

    protected $casts = [
        'can_read' => 'boolean',
        'can_act'  => 'boolean',
    ];

    public function transition()
    {
        return $this->belongsTo(WorkflowTransition::class, 'workflow_transition_id');
    }

    public function docProduit()
    {
        return $this->belongsTo(EtapeDocumentProduit::class, 'doc_produit_id');
    }

 
    public function workflowTransition()
    {
        return $this->belongsTo(WorkflowTransition::class, 'workflow_transition_id');
    }

     public function ua()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_id');
    }

}
