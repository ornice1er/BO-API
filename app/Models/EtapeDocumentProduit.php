<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class EtapeDocumentProduit extends Model
{
    use Filterable, HasFactory;

    private static $whiteListFilter = ['*'];

    protected $guarded = [];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }

    public function etapeEdition()
    {
        return $this->belongsTo(Etape::class, 'etape_edition_id');
    }

    public function etapeDelivrance()
    {
        return $this->belongsTo(Etape::class, 'etape_delivrance_id');
    }

    public function circuitEtapes()
{
    return $this->hasMany(DocumentCircuitEtape::class, 'doc_produit_id')
                ->orderBy('order');
}
}
