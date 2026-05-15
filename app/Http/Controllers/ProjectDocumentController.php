<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Common;
use App\Models\DocumentTemplate;
use App\Models\Project;
use App\Models\ProjectDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentController extends Controller
{
    // GET /api/projects/{projectId}/documents
    public function index($projectId)
    {
        $docs = ProjectDocument::with('template')
            ->where('project_id', $projectId)
            ->latest()
            ->get();

        return Common::success('Documents du projet', $docs);
    }

    // GET /api/projects/{projectId}/documents/init/{templateId}
    // Retourne (ou crée) le document projet associé au template choisi.
    // Réponse : { document, template }
    public function init($projectId, $templateId)
    {
        $project  = Project::findOrFail($projectId);
        $template = DocumentTemplate::findOrFail($templateId);

        $doc = ProjectDocument::firstOrCreate(
            ['project_id' => $projectId, 'template_id' => $templateId],
            ['title' => $template->name, 'status' => 'en_edition']
        );

        return Common::success('Document initialisé', [
            'document' => $doc->load('template'),
            'template' => $template,
        ]);
    }

    // POST /api/project-documents/{id}/save
    // Body : { title, html_content }
    public function save(Request $request, $id)
    {
        $doc = ProjectDocument::findOrFail($id);

        $doc->update([
            'title'   => $request->input('title', $doc->title),
            'content' => $request->input('html_content'),
        ]);

        return Common::success('Document sauvegardé', $doc->fresh('template'));
    }

    // POST /api/project-documents/{id}/generate
    // Génère le PDF à partir du contenu HTML stocké.
    public function generate($id)
    {
        try {
            $doc = ProjectDocument::with(['project', 'template'])->findOrFail($id);

            if (empty($doc->content)) {
                return Common::error('Le contenu du document est vide', []);
            }

            $data = [
                'title'   => $doc->title,
                'content' => $doc->content,
                'projet'  => $doc->project,
                'date'    => now()->format('d/m/Y'),
            ];

            $pdf      = Pdf::loadView('documents.projet.agrement', $data)->setPaper('a4', 'portrait');
            $filename = 'agrement_projet_' . $doc->project_id . '_' . time() . '.pdf';
            $path     = 'documents/projets/' . $doc->project_id . '/' . $filename;

            Storage::disk('public')->put($path, $pdf->output());

            $doc->update([
                'file_path' => $path,
                'file_url'  => Storage::disk('public')->url($path),
                'status'    => 'complet',
            ]);

            return Common::success('PDF généré avec succès', $doc->fresh('template'));

        } catch (\Throwable $th) {
            Log::error('ProjectDocument generate error: ' . $th->getMessage());
            return Common::error($th->getMessage(), []);
        }
    }
}
