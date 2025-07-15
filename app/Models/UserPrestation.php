<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPrestation extends Model
{
use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];

    protected $fillable = [
        'user_id',
        'prestation_id',
    ];

    /**
     * L'utilisateur lié à cette prestation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La prestation liée à cet utilisateur.
     */
    public function prestation()
    {
        return $this->belongsTo(Prestation::class);
    }
}

