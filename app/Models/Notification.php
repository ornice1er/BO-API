<?php

namespace App\Models;

use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Str;

class Notification extends Model
{
    use Filterable,HasFactory,HasUuids,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['id', 'type', 'notifiable_type', 'read_at', 'data', 'notifiable_id'];

    protected $dates = ['deleted_at'];

    protected $casts = ['data' => 'array'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            // $model->id = Str::uuid();
        });
    }
}
