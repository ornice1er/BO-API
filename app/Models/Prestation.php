<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Str;
use App\Models\WorkflowTransition;
class Prestation extends Model
{

    use Filterable;
    
    protected $guarded = [];


    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class,'unite_admin_id');
    }
    public function signer2()
    {
        return $this->belongsTo(UniteAdmin::class,'signer');
    }

    public function users()
    {
        return $this->hasMany(UserPrestation::class);
    }

    public function startPoints()
    {
        return $this->hasMany(StartPoint::class,'prestation_id');
    }
    public function startPoint2()
    {
        return $this->belongsTo(UniteAdmin::class,'start_point');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function workflowTransitions()
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    /** Prestation prérequise (ex. PS00928 pour la délivrance auto de PS00926). */
    public function sourcePrestation()
    {
        return $this->belongsTo(Prestation::class, 'source_prestation_id');
    }

    /**
     * Calculé dynamiquement : vrai si au moins une étape du workflow requiert un RDV.
     * Remplace le flag manuel sur la prestation.
     */
    public function getNeedMeetingAttribute(): bool
    {
        // Les étapes sont globales : la valeur qui fait foi est celle résolue pour
        // CETTE prestation (contextualisation `etape_prestations`, à défaut l'étape).
        $etapeIds = WorkflowTransition::where('prestation_id', $this->id)
            ->pluck('etape_to_id')
            ->filter()
            ->unique();

        foreach ($etapeIds as $etapeId) {
            if (EtapePrestation::resoudre($this->id, $etapeId)['need_meeting']) {
                return true;
            }
        }

        return false;
    }

      public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }
}
