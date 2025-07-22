<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function parcours()
    {
        return $this->hasMany(Parcours::class,'requete_id');
    }

   
    public function reponses()
    {
        return $this->hasMany(Reponse::class,'requete_id');
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



    public function adhcmps()
    {
        return $this->hasOne(AdherantCmps::class,'requete_id');
    }
    public function pccmps()
    {
        return $this->hasOne(PaiementCotisation::class,'requete_id');
    }
    public function ifur()
    {
        return $this->hasOne(ImmatriculationFichierUniqueReference::class,'requete_id');
    }
    public function cnr()
    {
        return $this->hasOne(CertificatDeNonRadiation::class,'requete_id');
    }
    public function vsa()
    {
        return $this->hasOne(ValidationDesServiceAux::class,'requete_id');
    }
    public function ascmps()
    {
        return $this->hasOne(AttestationDeSoin::class,'requete_id');
    }
}
