<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use Filterable, HasFactory;

    private static $whiteListFilter = ['*'];
    protected $guarded = [];
    protected $table = 'projects';

    protected $casts = [
        'request_ids' => 'array',
    ];

    /**
     * Get all requests associated with this project
     */
    public function requests()
    {
        return $this->belongsToMany(Requete::class, 'project_requete', 'project_id', 'requete_id')->withTimestamps();
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if project is closed
     */
    public function isClosed()
    {
        return $this->status === 'closed';
    }

    /**
     * Get the count of associated requests
     */
    public function getRequestCountAttribute()
    {
        return $this->requests()->count();
    }
}
