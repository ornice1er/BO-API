<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
class StartPoint extends Model
{

    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prestation_id',
        'unite_admin_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prestation_id' => 'integer',
        'unite_admin_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the prestation that owns the start point.
     */
    public function prestation(): BelongsTo
    {
        return $this->belongsTo(Prestation::class);
    }

    /**
     * Get the unite admin that owns the start point.
     */
    public function uniteAdmin(): BelongsTo
    {
        return $this->belongsTo(UniteAdmin::class);
    }

    /**
     * Scope a query to only include start points for a specific prestation.
     */
    public function scopeForPrestation($query, $prestationId)
    {
        return $query->where('prestation_id', $prestationId);
    }

    /**
     * Scope a query to only include start points for a specific unite admin.
     */
    public function scopeForUniteAdmin($query, $uniteAdminId)
    {
        return $query->where('unite_admin_id', $uniteAdminId);
    }
}
