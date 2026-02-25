<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Les membres de cette commission
     */
    public function members()
    {
        return $this->belongsToMany(Member::class, 'commission_members')
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
     * Les requêtes associées à cette commission
     */
    public function requetes()
    {
        return $this->belongsToMany(Requete::class, 'commission_requetes')
                    ->withPivot('global_mark', 'status')
                    ->withTimestamps();
    }

    /**
     * Les relations commission-requête
     */
    public function commissionRequetes()
    {
        return $this->hasMany(CommissionRequete::class);
    }

    /**
     * Les études de dossiers pour cette commission
     */
    public function etudeDossiers()
    {
        return $this->hasManyThrough(EtudeDossier::class, CommissionRequete::class);
    }

    /**
     * Scope pour les commissions actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les commissions fermées
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }
} 