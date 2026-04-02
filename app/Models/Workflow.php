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
    protected $appends = [];



        function etape() {
            return $this->belongsTo(Etape::class,'etape_id');
        }

           function prestation() {
            return $this->belongsTo(Prestation::class,'prestation_id');
        }

}
