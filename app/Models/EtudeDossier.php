<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EtudeDossier extends Model
{
    use HasFactory;

    protected $guarded = [];


    protected $casts = [
        'mark' => 'decimal:2',
    ];

    /**
     * Le membre de commission qui a effectué l'évaluation
     */
    public function commissionMember()
    {
        return $this->belongsTo(CommissionMember::class);
    }

    /**
     * La commission-requête évaluée
     */
    public function commissionRequete()
    {
        return $this->belongsTo(CommissionRequete::class);
    }

    /**
     * Le membre qui a effectué l'évaluation
     */
    public function member()
    {
        return $this->hasOneThrough(Member::class, CommissionMember::class, 'id', 'id', 'commission_member_id', 'member_id');
    }

    /**
     * La commission concernée
     */
    public function commission()
    {
        return $this->hasOneThrough(Commission::class, CommissionRequete::class, 'id', 'id', 'commission_requete_id', 'commission_id');
    }

    /**
     * La requête évaluée
     */
    public function requete()
    {
        return $this->hasOneThrough(Requete::class, CommissionRequete::class, 'id', 'id', 'commission_requete_id', 'requete_id');
    }

    /**
     * Scope pour les évaluations en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope pour les évaluations terminées
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope pour les évaluations avec note
     */
    public function scopeWithMark($query)
    {
        return $query->whereNotNull('mark');
    }
} 