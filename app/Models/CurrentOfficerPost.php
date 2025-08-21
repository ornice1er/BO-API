<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class CurrentOfficerPost extends Model
{
    use HasFactory, Filterable;

    private static $whiteListFilter = ['*'];

    protected $guarded = [];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_id');
    }

    public function fonction()
    {
        return $this->belongsTo(FonctionAgent::class, 'fonction_id');
    }
}
