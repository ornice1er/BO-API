<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class Workflow extends Model
{
    use Filterable,HasFactory;
    
    private static $whiteListFilter = ['*'];
    protected $guarded = [];
    protected $appends = ["next",'preview'];

       public $casts = [
            'next_eps' => 'array',
            'preview_eps' => 'array',
        ];


        function etape() {
            return $this->belongsTo(Etape::class,'etape_id');
        }

           function prestation() {
            return $this->belongsTo(Prestation::class,'prestation_id');
        }
 public function getNextAttribute()
    {
        if (empty($this->next_eps)) {
            return collect();
        }

        return EtapePrestationStatus::with(['etape','ps.status'])->whereIn('id', $this->next_eps)->get();
    }

    public function getPreviewAttribute()
    {
        if (empty($this->preview_eps)) {
            return collect();
        }

        return EtapePrestationStatus::with(['etape','ps.status'])->whereIn('id', $this->preview_eps)->get();
    }

}
