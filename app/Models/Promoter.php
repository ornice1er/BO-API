<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promoter extends Model
{
    use HasFactory;

    protected $table = 'promoters';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
    ];

    /**
     * Get the campaigns associated with the promoter.
     */
    public function campagnes()
    {
        return $this->hasMany(Campagne::class);
    }

    /**
     * Get the registrations associated with the promoter.
     */
    public function registrations()
    {
        return $this->hasMany(Inscription::class);
    }
}
