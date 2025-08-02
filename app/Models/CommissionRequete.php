<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommissionRequete extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'global_mark' => 'decimal:2',
    ];

    /**
     * La commission associée
     */
    public function commission()
    {
        return $this->belongsTo(Commission::class);
    }

    /**
     * La requête associée
     */
    public function requete()
    {
        return $this->belongsTo(Requete::class);
    }

    /**
     * Les études de dossiers pour cette commission-requête
     */
    public function etudeDossiers()
    {
        return $this->hasMany(EtudeDossier::class);
    }

    /**
     * Scope pour les requêtes en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les requêtes approuvées
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope pour les requêtes rejetées
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
} 