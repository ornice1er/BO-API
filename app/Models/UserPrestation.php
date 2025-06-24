<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPrestation extends Model
{
    use HasFactory;

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

