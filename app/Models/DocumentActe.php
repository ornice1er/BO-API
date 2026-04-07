<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentActe extends Model
{
    protected $table = 'document_actes';
    protected $guarded = [];
 
    protected $casts = [
        'generated_at'  => 'datetime',
        'completed_at'  => 'datetime',
    ];
 
    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }
 
    public function docProduit()
    {
        return $this->belongsTo(EtapeDocumentProduit::class, 'doc_produit_id');
    }
 
    public function currentCircuitStep()
    {
        return $this->belongsTo(DocumentCircuitEtape::class, 'current_circuit_step_id');
    }
 
    public function logs()
    {
        return $this->hasMany(DocumentActeLog::class, 'document_acte_id');
    }
}
