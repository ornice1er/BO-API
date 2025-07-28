<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

use Illuminate\Database\Eloquent\Model;

class RequeteFile extends Model
{

    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];

}
