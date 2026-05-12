<?php

namespace App\Http\Controllers;

use App\Models\DocumentActe;
use App\Models\DocumentActeLog;
use App\Models\DocumentCircuitEtape;
use App\Models\EtapeDocumentProduit;
use App\Models\Requete;
use App\Services\LogService;
use App\Utilities\Common;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\PNSService;


class DocumentActeController extends Controller
{
    protected $ls;

    public function __construct(LogService $ls)
    {
        $this->ls = $ls;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INITIALISER UN DOCUMENT ACTE
    // Appelé quand l'agent arrive sur l'étape d'édition
    // GET /api/document-actes/init/{requete_id}/{doc_produit_id}
    // ─────────────────────────────────────────────────────────────────────────
    public function init($requeteId, $docProduitId)
    {
        $message = 'Initialisation du document acte';

        try {
            $requete    = Requete::findOrFail($requeteId);
            $docProduit = EtapeDocumentProduit::with('circuitEtapes')->findOrFail($docProduitId);

            // Vérifier si un acte existe déjà pour cette requête + doc produit
            $acte = DocumentActe::where('requete_id',    $requeteId)
                                ->where('doc_produit_id', $docProduitId)
                                ->first();

            if (!$acte) {
                // Première étape du circuit
                $premiereEtape = $docProduit->circuitEtapes->sortBy('order')->first();

                // Générer le numéro d'identification
                $numero = $this->genererNumero($docProduit);

                $acte = DocumentActe::create([
                    'requete_id'             => $requeteId,
                    'doc_produit_id'         => $docProduitId,
                    'numero_identification'  => $numero,
                    'current_circuit_step_id'=> $premiereEtape?->id,
                    'status'                 => 'en_edition',
                    'generated_at'           => now(),
                ]);
            }

            // Charger le texte à trou depuis step_data de la requête
            $stepData = is_array($requete->step_data)
                ? $requete->step_data
                : json_decode($requete->step_data ?? '{}', true);
            $templateKey = $docProduit->template_key;

            // Préremplir les variables du template
            $variables = $this->extraireVariables($requete, $stepData, $templateKey);

            return Common::success($message, [
                'acte'       => $acte->load(['docProduit', 'currentCircuitStep']),
                'variables'  => $variables,
                'template'   => $templateKey,
                'doc_produit'=> $docProduit,
            ]);

        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GÉNÉRER LE PDF VIA TEMPLATE BLADE
    // POST /api/document-actes/{acteId}/generer
    // Body : { title, content, conclusion, variables: {} }
    // ─────────────────────────────────────────────────────────────────────────
    public function generer(Request $request, $acteId)
    {
        $message = 'Génération du document PDF';

        try {
            $acte = DocumentActe::with(['requete', 'docProduit'])->findOrFail($acteId);

            $variables = $request->input('variables', []);
            $content   = $request->input('content', '');

            // Remplacer les tokens {{clé}} par leurs valeurs réelles
            foreach ($variables as $key => $value) {
                $content = str_replace('{{' . $key . '}}', $value ?? '', $content);
            }

            $data = [
                'title'       => $request->input('title', ''),
                'content'     => $content,
                'conclusion'  => $request->input('conclusion', ''),
                'variables'   => $variables,
                'requete'     => $acte->requete,
                'acte'        => $acte,
                'numero'      => $acte->numero_identification,
                'date'        => now()->format('d/m/Y'),
            ];

            // Générer le PDF depuis le template Blade
            $templateKey = $acte->docProduit->template_key
                ?? 'documents.agrement.projet_lettre_agrement';

            $pdf = Pdf::loadView($templateKey, $data)
                      ->setPaper('a4', 'portrait');

            // Sauvegarder le fichier
            $filename  = $acte->numero_identification . '_' . time() . '.pdf';
            $path      = 'documents/' . $acte->requete->code . '/' . $filename;

            Storage::disk('public')->put($path, $pdf->output());

            // Sauvegarder le contenu édité dans l'acte
            $acte->update([
                'file_path'      => $path,
                'file_url'       => Storage::disk('public')->url($path),
                'content_data'   => json_encode($data),
                'status'         => 'en_edition',
                'generated_at'   => now(),
            ]);

            return Common::success($message, [
                'acte'     => $acte->fresh(['docProduit', 'currentCircuitStep']),
                'file_url' => Storage::disk('public')->url($path),
            ]);

        } catch (\Throwable $th) {
            Log::error('Génération PDF : ' . $th->getMessage());
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAUVEGARDER LE CONTENU WYSIWYG
    // POST /api/document-actes/{acteId}/sauvegarder
    // Body : { html_content, title, conclusion }
    //
    // Flux PNS (generate_from = 'pns') :
    //   1. On envoie le contenu HTML au PNS (decision = 'gendoc').
    //   2. Le PNS accuse réception mais NE retourne PAS l'URL du document.
    //   3. Le PNS génère le document en asynchrone et rappelle sur
    //      POST /eservices-recup-doc avec { code_demande, url }.
    //   4. EServiceRepository::recupDoc() télécharge le fichier et finalise l'acte.
    //   → Ici on sauvegarde le contenu, on passe en 'en_attente_pns' et on rend la main.
    //
    // Flux local (generate_from != 'pns') :
    //   Génération PDF immédiate via DomPDF + template Blade.
    // ─────────────────────────────────────────────────────────────────────────
    public function sauvegarder(Request $request, $acteId)
    {
        $message = "Sauvegarde du contenu WYSIWYG";

        try {
            $acte = DocumentActe::with(['requete', 'docProduit'])->findOrFail($acteId);

            $htmlContent = $request->input('html_content', '');
            $title       = $request->input('title', '');
            $conclusion  = $request->input('conclusion', '');

            if ($acte->docProduit->generate_from === 'pns') {
                // ── Envoi au PNS ──────────────────────────────────────────────
                $pnsService = new PNSService($acte->requete->header, [
                    'data'     => $htmlContent,
                    'message'  => 'Génération du document via PNS',
                    'status'   => false,
                    'decision' => 'gendoc',
                    'link'     => null,
                ]);
                $pnsResult = $pnsService->reply();

                info($pnsResult);

                // Sauvegarde du contenu en attente du callback PNS sur /eservices-recup-doc
                $acte->update([
                    'content_data' => json_encode([
                        'title'      => $title,
                        'content'    => $htmlContent,
                        'conclusion' => $conclusion,
                    ]),
                    'status' => 'en_attente_pns',
                ]);

                return Common::success('Document envoyé au PNS — en attente de génération', [
                    'acte'    => $acte->fresh(['docProduit', 'currentCircuitStep']),
                    'file_url'=> null,
                    'pending' => true,
                ]);
            }

            // ── Génération PDF locale ─────────────────────────────────────────
            $templateKey = $acte->docProduit->template_key
                ?? 'pdf.documents.projet_lettre_agrement';

            $data = [
                'title'      => $title,
                'content'    => $htmlContent,
                'conclusion' => $conclusion,
                'requete'    => $acte->requete,
                'acte'       => $acte,
                'numero'     => $acte->numero_identification,
                'date'       => now()->format('d/m/Y'),
            ];

            $pdf      = Pdf::loadView($templateKey, $data)->setPaper('a4', 'portrait');
            $filename = $acte->numero_identification . '_wysiwyg_' . time() . '.pdf';
            $path     = 'documents/' . $acte->requete->code . '/' . $filename;

            Storage::disk('public')->put($path, $pdf->output());

            $acte->update([
                'file_path'    => $path,
                'file_url'     => Storage::disk('public')->url($path),
                'content_data' => json_encode($data),
                'status'       => 'en_edition',
            ]);

            return Common::success($message, [
                'acte'     => $acte->fresh(['docProduit', 'currentCircuitStep']),
                'file_url' => Storage::disk('public')->url($path),
                'pending'  => false,
            ]);

        } catch (\Throwable $th) {
            Log::error('Sauvegarde WYSIWYG : ' . $th->getMessage());
           return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPLOADER UN PDF EXTERNE
    // POST /api/document-actes/{acteId}/upload
    // Body : multipart/form-data { file: PDF }
    // ─────────────────────────────────────────────────────────────────────────
    public function upload(Request $request, $acteId)
    {
        $message = 'Upload PDF externe';

        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf|max:10240',
            ]);

            $acte = DocumentActe::with(['requete', 'docProduit'])->findOrFail($acteId);

            $filename = $acte->numero_identification . '_upload_' . time() . '.pdf';
            $path     = 'documents/' . $acte->requete->code . '/' . $filename;

            Storage::disk('public')->putFileAs(
                'documents/' . $acte->requete->code,
                $request->file('file'),
                $filename
            );

            $acte->update([
                'file_path'    => $path,
                'file_url'     => Storage::disk('public')->url($path),
                'status'       => 'en_edition',
                'generated_at' => now(),
                'content_data' => null,
            ]);

            return Common::success($message, [
                'acte'     => $acte->fresh(['docProduit', 'currentCircuitStep']),
                'file_url' => Storage::disk('public')->url($path),
            ]);

        } catch (\Throwable $th) {
            Log::error('Upload PDF : ' . $th->getMessage());
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SOUMETTRE AU CIRCUIT (passer en_edition → en_circuit)
    // POST /api/document-actes/{acteId}/soumettre
    // ─────────────────────────────────────────────────────────────────────────
    public function soumettre(Request $request, $acteId)
    {
        $message = 'Soumission du document au circuit';

        try {
            $acte = DocumentActe::with([
                'requete', 'docProduit', 'currentCircuitStep'
            ])->findOrFail($acteId);

            if (!$acte->file_path) {
                return Common::error('Le document doit être généré avant soumission', []);
            }

            DB::beginTransaction();

            // Passer à l'étape suivante du circuit (order = 2 = paraphe DGT)
            $prochaineEtape = DocumentCircuitEtape::where('doc_produit_id', $acte->doc_produit_id)
                ->where('order', '>', $acte->currentCircuitStep->order)
                ->orderBy('order')
                ->first();

            $acte->update([
                'status'                  => 'en_circuit',
                'current_circuit_step_id' => $prochaineEtape?->id,
            ]);

            // Journaliser
            \App\Models\DocumentActeLog::create([
                'document_acte_id' => $acte->id,
                'circuit_step_id'  => $acte->currentCircuitStep->id,
                'action'           => 'edition',
                'triggered_by'     => \Auth::id(),
                'role_name'        => \Auth::user()->getRoleNames()->first(),
                'comment'          => $request->input('comment'),
                'acted_at'         => now(),
                'created_at'       => now(),
            ]);

            // Avancer le workflow de la requête via transition 'validation'
            // app(\App\Http\Repositories\RequeteRepository::class)
            //     ->avancerWorkflow($acte->requete, 'validation', [
            //         'comment' => 'Document soumis au circuit de signature',
            //     ]);

            DB::commit();

            return Common::success($message, [
                'acte' => $acte->fresh(['docProduit', 'currentCircuitStep', 'logs']),
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Soumission circuit : ' . $th->getMessage());
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS PRIVÉS
    // ─────────────────────────────────────────────────────────────────────────

    private function genererNumero(EtapeDocumentProduit $docProduit): string
    {
        $prefix  = $docProduit->numero_prefix ?? 'DOC';
        $annee   = now()->year;
        $dernier = DocumentActe::where('doc_produit_id', $docProduit->id)
            ->whereYear('created_at', $annee)
            ->count();

        return sprintf('%s-%d-%04d', $prefix, $annee, $dernier + 1);
    }

 private function extraireVariables(Requete $requete, array $stepData, string $templateKey): array
{
    $base = [
        'code'  => $requete->code,
        'email' => $requete->email,
        'phone' => $requete->phone,
        'date'  => now()->format('d/m/Y'),
        'annee' => now()->year,
    ];

    // step_contents peut être un array (déjà désérialisé par Laravel)
    // ou une string JSON selon le cast du modèle
    $contents = is_array($requete->step_contents)
        ? $requete->step_contents
        : json_decode($requete->step_contents ?? '[]', true);

    foreach ($contents as $step) {
        foreach ($step['content'] ?? [] as $key => $value) {
            $base[$key] = $value;
        }
    }

    return $base;
}

       // ─────────────────────────────────────────────────────────────────────────
    // RÉCUPÉRER LE DOC PRODUIT CONFIGURÉ POUR UNE ÉTAPE
    // GET /api/document-actes/doc-produit/{prestation_id}/{etape_id}
    // ─────────────────────────────────────────────────────────────────────────
    public function getDocProduit($prestationId, $etapeId)
    {
        $docProduit = EtapeDocumentProduit::where('prestation_id', $prestationId)
            ->where('etape_edition_id', $etapeId)
            ->first();

        return Common::success('Document produit', $docProduit);
    }
}

 