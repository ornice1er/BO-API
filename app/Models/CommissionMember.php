<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommissionMember extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * La commission associée
     */
    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }

    /**
     * Le membre associé
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Les études de dossiers effectuées par ce membre dans cette commission
     */
    public function etudeDossiers()
    {
        return $this->hasMany(EtudeDossier::class);
    }

    /**
     * Scope pour les membres actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 