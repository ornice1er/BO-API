<?php

namespace App\Models;

use App\Services\StatutAgentService;
use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable; // this sould be imported
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Filterable,HasApiTokens, HasFactory,Notifiable,SoftDeletes;

    // Liste blanche des attributs pouvant être filtrés
    private static $whiteListFilter = ['*'];

    // Les attributs qui sont mass-assignable
    protected $fillable = [
        'code',
        'name',
        'lastname',
        'firstname',
        'birthdate',
        'birthplace',
        'address',
        'phone',
        'photo',
        'is_active',
        'is_first_connexion',
        'email',
        'password',
        'token',
        'email_verified_at',
        'code_otp',
        'project_id',
        'spoken_languages',
        'understood_languages',
        'municipality_id',
        'statut_agent_id',
        'residence_place',
        'education_level',
        'nb_children',
        'computer_skills',
        'reference_person',
        'comment',
        'cv',
        'push_token',
    ];

    protected $casts = [
        'spoken_languages' => 'array',
        'understood_languages' => 'array',
    ];

    protected $appends = ['StatutAgent'];

    protected $dates = ['deleted_at'];

    /**
     * Fonction boot pour générer un code unique avant la création
     */
    public static function boot()
    {
        parent::boot();

        // Cette méthode est exécutée avant la création de chaque enregistrement
        self::creating(function ($model) {
            // Génération du code unique pour chaque utilisateur
            $model->code = (string) Core::generateIncrementUniqueCode('users', 3, 'code', null);

            $model->name = $model->lastname.' '.$model->firstname;
        });
    }

    public function setFirstnameAttribute($value)
    {
        $this->attributes['firstname'] = $value;
        $this->updateFullName();
    }

    public function setLastnameAttribute($value)
    {
        $this->attributes['lastname'] = $value;
        $this->updateFullName();
    }

    private function updateFullName()
    {
        $firstname = $this->attributes['firstname'] ?? '';
        $lastname = $this->attributes['lastname'] ?? '';

        $this->attributes['name'] = trim("$lastname $firstname");
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, UserProject::class, 'user_id', 'project_id');

    }

    public function userProjects()
    {
        return $this->hasMany(UserProject::class, 'user_id');

    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');

    }

    public function userProject()
    {
        return $this->hasOne(UserProject::class, 'user_id')->where('project_id', request()->project_id);

    }

    public function settings()
    {

        return $this->hasOne(UserSetting::class, 'user_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
        ];
    }

    public function getStatutAgentAttribute($model)
    {
        $ms = new StatutAgentService;
        if ($this->statut_agent_id) {
            return $ms->getStatutAgent($this->statut_agent_id)['data'];
        } else {
            return null;
        }

    }

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        info($this->push_token);

        return $this->push_token;
    }
}
