<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Contextualise une étape par prestation — pendant de `prestation_statuses`.
 *
 * Les étapes sont globales et partagées entre e-services. Leurs champs
 * comportementaux (SLA, unité responsable, RDV, association à une session) ne
 * peuvent donc pas être identiques pour tous : une même étape « Traitement »
 * peut demander 5 jours sur un visa et 30 sur un agrément.
 *
 * Les colonnes de `etapes` restent en place et servent de **valeurs par défaut** :
 * la résolution lit d'abord le pivot pour la prestation courante, puis retombe sur
 * l'étape (@see \App\Models\EtapePrestation::resoudre()).
 *
 * Le backfill recopie l'existant pour chaque couple (prestation, étape) réellement
 * présent dans le graphe de transitions : le comportement du jour du déploiement
 * est reproduit à l'identique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etape_prestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestation_id');
            $table->foreignId('etape_id');

            // Champs contextuels — null = « hérite de l'étape »
            $table->integer('sla_days')->nullable();
            $table->foreignId('unite_admin_id')->nullable();
            $table->boolean('can_associate')->nullable();
            $table->boolean('need_meeting')->nullable();

            $table->timestamps();

            $table->unique(['prestation_id', 'etape_id'], 'etape_prestations_unique');
            $table->index('prestation_id');
        });

        $this->backfill();
    }

    /**
     * Une ligne par couple (prestation, étape) apparaissant dans le graphe de
     * transitions, en recopiant les valeurs actuellement portées par l'étape.
     */
    private function backfill(): void
    {
        if (!Schema::hasTable('workflow_transitions') || !Schema::hasTable('etapes')) {
            return;
        }

        $couples = DB::table('workflow_transitions')
            ->select('prestation_id', 'etape_from_id as etape_id')
            ->whereNotNull('etape_from_id')
            ->union(
                DB::table('workflow_transitions')
                    ->select('prestation_id', 'etape_to_id as etape_id')
                    ->whereNotNull('etape_to_id')
            )
            ->get()
            ->unique(fn($r) => $r->prestation_id . '-' . $r->etape_id);

        $etapes = DB::table('etapes')->get()->keyBy('id');
        $now    = now();
        $rows   = [];

        foreach ($couples as $c) {
            $etape = $etapes->get($c->etape_id);
            if (!$etape) continue;

            $rows[] = [
                'prestation_id'  => $c->prestation_id,
                'etape_id'       => $c->etape_id,
                'sla_days'       => $etape->sla_days       ?? null,
                'unite_admin_id' => $etape->unite_admin_id ?? null,
                'can_associate'  => $etape->can_associate  ?? null,
                'need_meeting'   => $etape->need_meeting   ?? null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('etape_prestations')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('etape_prestations');
    }
};
