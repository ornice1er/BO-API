<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requete extends Model
{
    protected $guarded = [];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class,'prestation_id');
    }

    public function parcours()
    {
        return $this->hasMany(Parcours::class,'requete_id');
    }

    public function atn()
    {
        return $this->hasOne(AttestationNonLitige::class,'requete_id');
    }


    public function detab()
    {
        return $this->hasOne(DeclarationEtablissement::class,'requete_id');
    }

    public function asf()
    {
        return $this->hasOne(AttestationDeServiceFait::class,'requete_id');
    }

    public function ads()
    {
        return $this->hasOne(AutorisationDeStage::class,'requete_id');
    }


    public function as()
    {
        return $this->hasOne(AttestationDeStage::class,'requete_id');
    }

    public function ri()
    {
        return $this->hasOne(VisaRIE::class,'requete_id');
    }
    public function ca()
    {
        return $this->hasOne(VisaCA::class,'requete_id');
    }
    public function al()
    {
        return $this->hasOne(AutorisationLicenciement::class,'requete_id');
    }
    public function am()
    {
        return $this->hasOne(AgrementMedecin::class,'requete_id');
    }
    public function adpap()
    {
        return $this->hasOne(AttestationDePresenceAuPoste::class,'requete_id');
    }

    public function ae()
    {
        return $this->hasOne(AttestationExistence::class,'requete_id');
    }

    public function reponses()
    {
        return $this->hasMany(Reponse::class,'requete_id');
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
