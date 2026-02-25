<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Les commissions auxquelles appartient ce membre
     */
    public function commissions()
    {
        return $this->belongsToMany(Commission::class, 'commission_members')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    /**
     * Les relations commission-membre
     */
    public function commissionMembers()
    {
        return $this->hasMany(CommissionMember::class);
    }

    /**
     * Les études de dossiers effectuées par ce membre
     */
    public function etudeDossiers()
    {
        return $this->hasManyThrough(EtudeDossier::class, CommissionMember::class);
    }

    /**
     * Nom complet du membre
     */
    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }
} 