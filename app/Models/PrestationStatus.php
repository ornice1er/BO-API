<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class PrestationStatus extends Model
{
    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];

    function prestation() {
      
    return $this->belongsTo(Prestation::class, 'prestation_id');
    }

     function status() {
      
    return $this->belongsTo(Status::class, 'status_id');
    }
 
 }
