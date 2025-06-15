<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    public $timestamps = true;

    private static $whiteListFilter = ['*'];

    use HasFactory;

    protected $fillable = [
        'email',
        'phone',
        'code',
        'sms',
        'whatsapp',
        'expired_at',
    ];
}
