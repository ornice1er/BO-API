<?php

namespace App\Models;

use Auth;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Log extends Model
{
    use Filterable, HasFactory,SoftDeletes;

    private static $whiteListFilter = ['*'];

    protected $fillable = ['action_name', 'description', 'done_by'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->done_by = Auth::id() ?? null;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
