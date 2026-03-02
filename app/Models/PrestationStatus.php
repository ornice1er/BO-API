<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class PrestationStatus extends Model
{
      use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    use HasFactory;
}
