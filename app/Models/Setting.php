<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'durer',
        'seance_interactive',
        'dure_deuiduite',
    ];
}
