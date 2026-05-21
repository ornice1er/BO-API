<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;


class Requete extends Model
{

    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    protected $guarded = [];

  public $casts = [
            'step_contents' => 'array',
            'header' => 'array',
        ];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class,'prestation_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class,'project_id');
    }

    public function parcours()
    {
        return $this->hasMany(Parcours::class,'requete_id');
    }

   
    public function reponses()
    {
        return $this->hasMany(Reponse::class,'requete_id');
    }
    public function lastReponse()
        {
            return $this->hasOne(Reponse::class, 'requete_id')
                        ->latestOfMany(); // basé sur created_at
        }
        public function userReponse()
        {
            return $this->hasOne(Reponse::class, 'requete_id');
        }

            public function files()
    {
        return $this->hasMany(RequeteFile::class,'requete_id');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class,'requete_id');
    }


    public function affectation()
    {
        return $this->hasOne(Affectation::class,'requete_id')->where('isLast',true);
    }

// Dans app/Models/Requete.php — ajouter si absent
public function currentEtape()    { return $this->belongsTo(Etape::class, 'current_etape_id'); }
public function currentStatus()   { return $this->belongsTo(Status::class, 'current_status_id'); }
public function requeteEtapeLogs(){ return $this->hasMany(RequeteEtapeLog::class); }
public function documentActes()   { return $this->hasMany(DocumentActe::class); }
public function agendas()         { return $this->hasMany(Agenda::class, 'requete_id'); }
public function planningSlot()    { return $this->belongsTo(\App\Models\PlanningSlot::class, 'planning_slot_id'); }
    /**
     * Les commissions auxquelles cette requête est associée
     */
    public function commissions()
    {
        return $this->belongsToMany(Commission::class, 'commission_requetes')
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
     * Les études de dossiers pour cette requête
     */
    public function etudeDossiers()
    {
        return $this->hasManyThrough(EtudeDossier::class, CommissionRequete::class);
    }

    /**
     * The projects that this request belongs to
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_requete', 'requete_id', 'project_id')->withTimestamps();
    }





// Dernier log (utilisateur qui a effectué la dernière action)
public function lastLog()
{
    return $this->hasOne(\App\Models\RequeteEtapeLog::class)
                ->latestOfMany('transitioned_at');
}
}
