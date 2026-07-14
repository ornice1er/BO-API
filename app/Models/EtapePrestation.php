<?php

namespace App\Models;

use eloquentFilter\QueryFilter\ModelFilters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Contextualisation d'une étape (globale) pour une prestation donnée.
 *
 * Un champ `null` signifie « hérite de la valeur portée par l'étape ».
 */
class EtapePrestation extends Model
{
    use Filterable, HasFactory;

    private static $whiteListFilter = ['*'];

    protected $table = 'etape_prestations';

    protected $guarded = [];

    protected $casts = [
        'can_associate' => 'boolean',
        'need_meeting'  => 'boolean',
        'sla_days'      => 'integer',
    ];

    /** Champs dont la valeur peut différer d'une prestation à l'autre. */
    public const CHAMPS_CONTEXTUELS = ['sla_days', 'unite_admin_id', 'can_associate', 'need_meeting'];

    public function prestation()
    {
        return $this->belongsTo(Prestation::class, 'prestation_id');
    }

    public function etape()
    {
        return $this->belongsTo(Etape::class, 'etape_id');
    }

    public function uniteAdmin()
    {
        return $this->belongsTo(UniteAdmin::class, 'unite_admin_id');
    }

    /**
     * Valeurs effectives d'une étape pour une prestation : le pivot d'abord,
     * la valeur portée par l'étape ensuite.
     *
     * @return array{sla_days:?int, unite_admin_id:?int, can_associate:bool, need_meeting:bool}
     */
    public static function resoudre(?int $prestationId, ?int $etapeId): array
    {
        $defauts = [
            'sla_days'       => null,
            'unite_admin_id' => null,
            'can_associate'  => false,
            'need_meeting'   => false,
        ];

        if (!$etapeId) {
            return $defauts;
        }

        $etape = Etape::find($etapeId);
        if (!$etape) {
            return $defauts;
        }

        $valeurs = [
            'sla_days'       => $etape->sla_days,
            'unite_admin_id' => $etape->unite_admin_id,
            'can_associate'  => (bool) $etape->can_associate,
            'need_meeting'   => (bool) $etape->need_meeting,
        ];

        if (!$prestationId) {
            return $valeurs;
        }

        $override = static::where('prestation_id', $prestationId)
            ->where('etape_id', $etapeId)
            ->first();

        if (!$override) {
            return $valeurs;
        }

        foreach (static::CHAMPS_CONTEXTUELS as $champ) {
            if (!is_null($override->{$champ})) {
                $valeurs[$champ] = $override->{$champ};
            }
        }

        $valeurs['can_associate'] = (bool) $valeurs['can_associate'];
        $valeurs['need_meeting']  = (bool) $valeurs['need_meeting'];

        return $valeurs;
    }
}
