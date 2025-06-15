<?php

namespace App\Models;

use App\Utilities\Core;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSetting extends Model
{
    use Filterable, HasFactory,SoftDeletes;

    // Liste blanche des filtres (tous les champs sont filtrables)
    private static $whiteListFilter = ['*'];

    // Attributs remplissables
    protected $fillable = [
        'user_id',
        'use_2FA',
        'accept_notification',
        'notification_list',
        'mode_2FA',
    ];

    protected $casts = ['notification_list' => 'array'];

    protected $dates = ['deleted_at'];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Méthode boot pour ajouter des comportements lors de la création
     */
    public static function boot()
    {
        parent::boot();

        // Exemples de comportements personnalisés lors de la création
        // self::creating(function ($model) {
        //     $model->code = (string) Core::generateIncrementUniqueCode('user_settings', 3, 'code', null);
        // });
    }

    /**
     * Obtenir la liste des notifications sous forme de tableau
     */
}
