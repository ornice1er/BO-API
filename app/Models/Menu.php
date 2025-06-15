<?php

namespace App\Models;

use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use Filterable,HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['name', 'key', 'path', 'is_active'];

    protected $dates = ['deleted_at'];
}
