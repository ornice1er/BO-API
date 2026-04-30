<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlanningSlot extends Model
{
    use HasFactory;

    protected $table = 'planning_slots';
    protected $guarded = [];

    protected $appends = ['slots_remaining', 'is_full'];

    public function getSlotsRemainingAttribute(): int
    {
        return max(0, $this->max_slots - $this->slots_booked);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->slots_booked >= $this->max_slots;
    }

    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_id');
    }

    public function prestation()
    {
        return $this->belongsTo(Prestation::class, 'prestation_id');
    }

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
                     ->whereColumn('slots_booked', '<', 'max_slots');
    }
}
