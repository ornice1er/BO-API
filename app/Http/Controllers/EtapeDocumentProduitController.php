<?php

namespace App\Http\Controllers;

use App\Http\Repositories\EtapeDocumentProduitRepository;
use App\Http\Requests\EtapeDocumentProduit\StoreEtapeDocumentProduitRequest;
use App\Http\Requests\EtapeDocumentProduit\UpdateEtapeDocumentProduitRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;

class EtapeDocumentProduitController extends Controller
{
    protected $repository;
    protected $ls;

    public function __construct(EtapeDocumentProduitRepository $repository, LogService $ls)
    {
        $this->repository = $repository;
        $this->ls = $ls;
    }

    public function index(Request $request)
    {
        $message = 'Récupération des documents produits';

        try {
            $result = $this->repository->getAll($request);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function show($id)
    {
        $message = 'Récupération d\'un document produit';

        try {
            $result = $this->repository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function store(StoreEtapeDocumentProduitRequest $request)
    {
        $message = 'Enregistrement d\'un document produit';

        try {
            $result = $this->repository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::successCreate('Document produit créé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function update(UpdateEtapeDocumentProduitRequest $request, $id)
    {
        $message = 'Mise à jour d\'un document produit';

        try {
            $result = $this->repository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);

            return Common::success('Document produit mis à jour avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'un document produit';

        try {
            $recup = $this->repository->get($id);
            $result = $this->repository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);

            return Common::successDelete('Document produit supprimé avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }

    public function search(Request $request)
    {
        $message = 'Recherche de documents produits';

        try {
            $result = $this->repository->search($request->search);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);

            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);

            return Common::error($th->getMessage(), []);
        }
    }
}
