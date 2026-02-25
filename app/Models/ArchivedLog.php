<?php

namespace App\Models;

use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivedLog extends Model
{
    use Filterable, HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['start_date', 'end_date', 'file_path'];
}