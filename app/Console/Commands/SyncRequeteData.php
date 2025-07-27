<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Storage;

class SyncRequeteData extends Command
{
    protected $signature = 'sync:requete-data';

    protected $description = 'Transforme les données des tables métiers vers la table requetes (step_contents + fichiers)';

    public function handle(): void
    {
        // Liste des tables métier avec leurs fichiers associés et clé étrangère personnalisée
        $tables = [
            'agrement_medecins' => ['file_table' => 'agrement_medecin_files', 'foreign_key' => 'am_id'],
            'attestation_de_presence_au_postes' => ['file_table' => 'attestation_de_presence_au_poste_files', 'foreign_key' => 'adpap_id'],
            'attestation_de_service_faits' => ['file_table' => 'attestation_de_service_fait_files', 'foreign_key' => 'asf_id'],
            'attestation_non_litiges' => ['file_table' => 'attestation_non_litige_files', 'foreign_key' => 'atn_id'],
            'visa_c_a_s' => ['file_table' => 'visa_c_a_files', 'foreign_key' => 'visa_ca_id'],
            'visa_r_i_e_s' => ['file_table' => 'visa_r_i_e_files', 'foreign_key' => 'visa_rie_id'],
        ];

        foreach ($tables as $table => $config) {
            $rows = DB::table($table)->get();

            foreach ($rows as $row) {
                // Récupération de la requête
                $requete = DB::table('requetes')->where('id', $row->requete_id)->first();
                if (!$requete) {
                    $this->warn("Aucune requête trouvée pour {$table} ID {$row->id}");
                    continue;
                }

                $prestation = DB::table('prestations')->where('id', $requete->prestation_id)->first();
                if (!$prestation) {
                    $this->warn("Aucune prestation trouvée pour requête {$requete->id}");
                    continue;
                }

                $rawData = (array) $row;

                // Champs à intégrer dans la table requetes également
                $metaFields = ['lastname','firstname','ifu', 'npi','rccm','identity', 'message','date_job','birthdate','raison_sociale','function'];

                $metaData = [];
                foreach ($metaFields as $field) {
                    if (isset($rawData[$field])) {
                        $metaData[$field] = $rawData[$field];
                    }
                }

                $stepContents = [
                    'steps' => [
                        [
                            'title' => 'Information sur la demande',
                            'content' => $rawData,
                        ],
                    ],
                    'meta' => [
                        'code' => $requete->code,
                        'prestation_code' => $prestation->code,
                        'info' => $metaData,
                    ],
                ];

                // Mise à jour dans la table requetes
                DB::table('requetes')->where('id', $requete->id)->update(array_merge(
                    $metaData,
                    [
                        'step_contents' => json_encode($stepContents),
                        'updated_at' => now(),
                    ]
                ));

                // Import des fichiers si une table est configurée
                $fileTable = $config['file_table'] ?? null;
                $foreignKey = $config['foreign_key'] ?? "{$table}_id";

                if ($fileTable) {
                    $fileRows = DB::table($fileTable)->where($foreignKey, $row->id)->get();

                    foreach ($fileRows as $file) {
                        DB::table('requete_files')->updateOrInsert(
                            [
                                'requete_id' => $requete->id,
                                'name' => $file->filename,
                            ],
                            [
                                'url' => Storage::disk('public')->url($prestation->code.'/'.$requete->id.'/'.$file->filename),
                                'file_path' => $file->file_path ?? $file->path ?? null,
                                'file_type' => $file->type ?? null,
                                'updated_at' => now(),
                                'created_at' => now(), // Cette ligne n’est utile que si la ligne est insérée
                            ]
                        );
                    }
                }

                $this->info("Traitement de {$table} ID {$row->id} vers requête {$requete->code} terminé.");
            }
        }

        $this->info("Synchronisation terminée pour toutes les tables.");
    }
}
