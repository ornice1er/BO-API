<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Affectation extends Model
{

    use Filterable,HasFactory;
    private static $whiteListFilter = ['*'];

    protected $guarded = [];

    public function requete()
    {
        return $this->belongsTo(Requete::class, 'requete_id');
    }

    public function uniteAdminUp()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_up');
    }

    public function uniteAdminDown()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_down');
    }

    public function copUp()
    {
        return $this->belongsTo(CurrentOfficerPost::class, 'cop_up');
    }

    public function copDown()
    {
        return $this->belongsTo(CurrentOfficerPost::class, 'cop_down');
    }
}
