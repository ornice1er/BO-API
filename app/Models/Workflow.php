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
            'next_etapes' => 'array',
            'preview_etapes' => 'array',
        ];


        function etape() {
            return $this->belongsTo(Etape::class,'etape_id');
        }

           function prestation() {
            return $this->belongsTo(Prestation::class,'prestation_id');
        }
 public function getNextAttribute()
    {
        if (empty($this->next_etapes)) {
            return collect();
        }

        $prestationId=$this->prestation_id;
        return EtapePrestationStatus::with(['etape','ps.status'])->whereIn('etape_id', $this->next_etapes)->whereHas('ps',function($q)use($prestationId){$q->where('prestation_id',$prestationId);})->get();
    }

    public function getPreviewAttribute()
    {
        if (empty($this->preview_etapes)) {
            return collect();
        }

         $prestationId=$this->prestation_id;
        return EtapePrestationStatus::with(['etape','ps.status'])->whereIn('etape_id', $this->preview_etapes)->whereHas('ps',function($q)use($prestationId){$q->where('prestation_id',$prestationId);})->get();
    }

}
