<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class EtapePrestationStatus extends Model
{
      use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
 protected $guarded = [];
 
 

     function ps() {
      
    return $this->belongsTo(PrestationStatus::class, 'ps_id');
    }

     function etape() {
      
    return $this->belongsTo(Etape::class, 'etape_id');
    }
 
 }
