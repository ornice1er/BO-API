<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearPrestationRequetes extends Command
{
    protected $signature = 'prestation:clear-requetes
                            {code : Code de la prestation}
                            {--force : Ignorer la confirmation}';

    protected $description = 'Vider toutes les requêtes (et leurs données) d\'une prestation sans toucher à la configuration';

    public function handle(): int
    {
        $code = $this->argument('code');

        $pid = DB::table('prestations')->where('code', $code)->value('id');
        if (!$pid) {
            $this->error("Prestation introuvable : $code");
            return Command::FAILURE;
        }

        $name  = DB::table('prestations')->where('id', $pid)->value('name');
        $count = DB::table('requetes')->where('prestation_id', $pid)->count();

        if ($count === 0) {
            $this->info("Aucune requête à supprimer pour « $name ».");
            return Command::SUCCESS;
        }

        $this->warn("Prestation  : $name ($code)");
        $this->warn("Requêtes    : $count à supprimer");

        if (!$this->option('force') && !$this->confirm('Confirmer la suppression ?', false)) {
            $this->info('Annulé.');
            return Command::SUCCESS;
        }

        $requeteIds = DB::table('requetes')->where('prestation_id', $pid)->pluck('id');

        DB::beginTransaction();
        try {
            // Données liées aux requêtes
            DB::table('requete_etape_logs')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('document_actes')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('commission_requetes')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('reponses')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('affectations')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('agendas')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('parcours')->whereIn('requete_id', $requeteIds)->delete();
            DB::table('project_requete')->whereIn('requete_id', $requeteIds)->delete();

            // Fichiers physiques + enregistrements
            $files = DB::table('requete_files')->whereIn('requete_id', $requeteIds)->pluck('file_path');
            foreach ($files as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::table('requete_files')->whereIn('requete_id', $requeteIds)->delete();

            // Dossiers de stockage (un dossier par code requête)
            $codes = DB::table('requetes')->whereIn('id', $requeteIds)->pluck('code');
            foreach ($codes as $reqCode) {
                Storage::disk('public')->deleteDirectory($reqCode);
            }

            // Requêtes
            DB::table('requetes')->where('prestation_id', $pid)->delete();

            DB::commit();

            $this->info("✓ $count requête(s) supprimée(s) pour « $name ».");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
