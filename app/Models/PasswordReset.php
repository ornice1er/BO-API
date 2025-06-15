<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'password_reset_tokens';

    protected $primaryKey = 'email';  // Définir la clé primaire correcte

    public $incrementing = false;     // Désactiver l'auto-incrémentation (pour éviter l'usage d'un 'id')

    protected $keyType = 'string';

    public $timestamps = false;
}
