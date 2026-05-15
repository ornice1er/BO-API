<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Common;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;

class DocumentTemplateController extends Controller
{
    public function index()
    {
        return Common::success('Templates', DocumentTemplate::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'type'    => 'nullable|string|max:50',
            'content' => 'nullable|string',
        ]);

        $template = DocumentTemplate::create($request->only('name', 'type', 'content', 'is_active'));
        return Common::successCreate('Template créé', $template);
    }

    public function show($id)
    {
        return Common::success('Template', DocumentTemplate::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'    => 'sometimes|string|max:255',
            'type'    => 'nullable|string|max:50',
            'content' => 'nullable|string',
        ]);

        $template = DocumentTemplate::findOrFail($id);
        $template->update($request->only('name', 'type', 'content', 'is_active'));
        return Common::success('Template mis à jour', $template);
    }

    public function destroy($id)
    {
        DocumentTemplate::findOrFail($id)->delete();
        return Common::success('Template supprimé', []);
    }
}
