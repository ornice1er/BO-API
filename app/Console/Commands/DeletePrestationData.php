<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeletePrestationData extends Command
{
    protected $signature = 'prestation:purge {code}';
    protected $description = 'Supprimer toutes les données liées à une prestation';

    public function handle()
    {
        $code = $this->argument('code');

        DB::beginTransaction();

        try {
            $pid = DB::table('prestations')->where('code', $code)->value('id');

            if (!$pid) {
                $this->error("Prestation introuvable pour le code : $code");
                return Command::FAILURE;
            }

            // 1. Notifications
            // DB::table('etape_notifications')
            //     ->whereIn('workflow_transition_id', function ($q) use ($pid) {
            //         $q->select('id')->from('workflow_transitions')->where('prestation_id', $pid);
            //     })->delete();

            // 2. Visibilités
            // DB::table('etape_visibilites')
            //     ->whereIn('workflow_transition_id', function ($q) use ($pid) {
            //         $q->select('id')->from('workflow_transitions')->where('prestation_id', $pid);
            //     })->delete();

            // 3. Transitions
            DB::table('workflow_transitions')->where('prestation_id', $pid)->delete();

            // 4. Workflows
            DB::table('workflows')->where('prestation_id', $pid)->delete();

            // 5. Circuit signature
            DB::table('document_circuit_etapes')
                ->whereIn('doc_produit_id', function ($q) use ($pid) {
                    $q->select('id')->from('etape_documents_produits')->where('prestation_id', $pid);
                })->delete();

            // 6. Documents produits
            DB::table('etape_documents_produits')->where('prestation_id', $pid)->delete();

            // 7. Pièces justificatives
            DB::table('etape_documents')->where('prestation_id', $pid)->delete();

            // 8. Motifs rejet
            DB::table('motifs_rejet')->where('prestation_id', $pid)->delete();

            // 9. Statuts
            DB::table('prestation_statuses')->where('prestation_id', $pid)->delete();

            // 10. Start points
            DB::table('start_points')->where('prestation_id', $pid)->delete();

            // 11. Logs requêtes
            DB::table('requete_etape_logs')
                ->whereIn('requete_id', function ($q) use ($pid) {
                    $q->select('id')->from('requetes')->where('prestation_id', $pid);
                })->delete();

            // 12. Fichiers requêtes
            DB::table('requete_files')
                ->whereIn('requete_id', function ($q) use ($pid) {
                    $q->select('id')->from('requetes')->where('prestation_id', $pid);
                })->delete();

            // 13. Parcours
            DB::table('parcours')
                ->whereIn('requete_id', function ($q) use ($pid) {
                    $q->select('id')->from('requetes')->where('prestation_id', $pid);
                })->delete();

            // 14. Requêtes
            DB::table('requetes')->where('prestation_id', $pid)->delete();

            // 15. Prestation
            DB::table('prestations')->where('id', $pid)->delete();

            DB::commit();

            $this->info("Suppression complète terminée pour la prestation : $code");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur : " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}