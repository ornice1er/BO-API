<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentCircuitEtape extends Model
{
    use HasFactory;

    protected $table = 'document_circuit_etapes';

    protected $guarded = [];

    public function docProduit()
    {
        return $this->belongsTo(EtapeDocumentProduit::class, 'doc_produit_id');
    }

    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_id');
    }
}
