<?php

namespace App\Http\Controllers;

use App\Http\Repositories\RequeteRepository;
use App\Http\Requests\Requete\StoreRequeteRequest;
use App\Http\Requests\Requete\UpdateRequeteRequest;
use App\Services\LogService;
use App\Utilities\Common;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class RequeteController extends Controller
{
    protected $requeteRepository;
    protected $ls;

    public function __construct(RequeteRepository $requeteRepository, LogService $ls)
    {
        $this->requeteRepository = $requeteRepository;
        $this->ls = $ls;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LECTURE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Liste complète (admin / superviseur).
     * GET /requetes
     */
    public function index(Request $request)
    {
        $message = 'Récupération de la liste des requêtes';
        try {
            $result = $this->requeteRepository->getAll($request->all());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Banette de l'agent connecté selon son rôle et les visibilités configurées.
     * Remplace toutes les anciennes banettes hardcodées.
     * GET /requetes/banette/{prestation_code}
     */
    public function getBanette($prestation_code)
    {
        $message = 'Récupération de la banette';
        try {
            $result = $this->requeteRepository->getBanette($prestation_code);
            $this->ls->trace(['action_name' => $message, 'description' => $prestation_code]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Détail complet d'une requête par code.
     * GET /requetes/one/{code}
     */
    public function getOne(Request $request, $code)
    {
        $message = 'Récupération du détail d\'une requête';
        try {
            $result = $this->requeteRepository->getOne(['code' => $code]);
            $this->ls->trace(['action_name' => $message, 'description' => $code]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Suivi public par le requérant (sans authentification).
     * GET /requetes/suivi/{code}
     */
    public function getSuivi($code)
    {
        $message = 'Suivi de la demande';
        try {
            $result = $this->requeteRepository->getSuivi($code);
            $this->ls->trace(['action_name' => $message, 'description' => $code]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Vérification de la complétude du dossier.
     * GET /requetes/{id}/completude
     */
    public function verifierCompletude($id)
    {
        $message = 'Vérification complétude dossier';
        try {
            $requete = $this->requeteRepository->get($id);
            $result  = $this->requeteRepository->verifierCompletude($requete);
            $this->ls->trace(['action_name' => $message, 'description' => $id]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Vérifier si l'agent connecté peut agir sur une requête.
     * GET /requetes/{id}/peut-agir
     */
    public function peutAgir($id)
    {
        $message = 'Vérification autorisation action';
        try {
            $requete = $this->requeteRepository->get($id);
            $result  = $this->requeteRepository->peutAgir($requete);
            return Common::success($message, ['peut_agir' => $result]);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

      /**
     * Vérifier si l'agent connecté peut agir sur une requête.
     * GET /requetes/{id}/peut-agir
     */
    public function getForAgenda($code)
    {
        $message = 'Récupération des données pour l\'agenda';
        try {
            $result  = $this->requeteRepository->getForAgenda($code);
            return Common::success($message,  $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Détail par ID.
     * GET /requetes/{id}
     */
    public function show(Request $request, $id)
    {
        $message = 'Récupération d\'une requête';
        try {
            $result = $this->requeteRepository->get($id);
            $this->ls->trace(['action_name' => $message, 'description' => $id]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIONS WORKFLOW
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Prise en charge par l'agent.
     * POST /requetes/{id}/prendre-en-charge
     */
    public function prendreEnCharge(Request $request, $id)
    {
        $message = 'Prise en charge de la requête';
        try {
            $result = $this->requeteRepository->prendreEnCharge($id);
            $this->ls->trace(['action_name' => $message, 'description' => $id]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Traiter une demande : valider, rejeter, signer, parapher, prévalider, clôturer.
     *
     * Body JSON :
     * {
     *   "decision": "valider|rejeter|signer|parapher|prevalider|cloturer|completer",
     *   "comment":  "Motif ou commentaire libre",
     *   "motif_id": 3,
     *   "metadata": {}
     * }
     *
     * POST /requetes/{id}/traiter
     */
    public function traiter(Request $request, $id)
    {
        $message = 'Traitement de la requête';
        try {
            $decision = $request->input('decision');
            $options  = [
                'comment'  => $request->input('comment'),
                'motif_id' => $request->input('motif_id'),
                'metadata' => $request->input('metadata', []),
            ];

            $result = $this->requeteRepository->traiterDemande($id, $decision, $options);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode(['id' => $id, 'decision' => $decision])]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Action sur le circuit documentaire (paraphe, signature, prévalidation).
     *
     * Body JSON :
     * {
     *   "action":    "paraphe|signature|prevalidation|edition",
     *   "comment":   "Note facultative",
     *   "file_path": "chemin/vers/document/signe.pdf",
     *   "metadata":  {}
     * }
     *
     * POST /requetes/documents/{acte_id}/traiter
     */
    public function traiterDocument(Request $request, $acteId)
    {
        $message = 'Action sur le circuit documentaire';
        try {
            $options = [
                'action'    => $request->input('action'),
                'comment'   => $request->input('comment'),
                'file_path' => $request->input('file_path'),
                'metadata'  => $request->input('metadata', []),
            ];

            $result = $this->requeteRepository->traiterDocument($acteId, $options['action'], $options);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode(['acte_id' => $acteId, 'action' => $options['action']])]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /**
     * Correction d'une demande rejetée par le requérant.
     *
     * Body JSON :
     * {
     *   "step_contents": {},
     *   "step_data":     {}
     * }
     *
     * POST /requetes/{id}/corriger
     */
    public function corriger(Request $request, $id)
    {
        $message = 'Correction de la demande';
        try {
            $result = $this->requeteRepository->corrigerDemande($id, $request->all());
            $this->ls->trace(['action_name' => $message, 'description' => $id]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CRUD STANDARD
    // ─────────────────────────────────────────────────────────────────────────

    public function store(StoreRequeteRequest $request)
    {
        $message = 'Enregistrement d\'une requête';
        try {
            $result = $this->requeteRepository->makeStore($request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);
            return Common::successCreate('Requête créée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function update(UpdateRequeteRequest $request, $id)
    {
        $message = 'Mise à jour d\'une requête';
        try {
            $result = $this->requeteRepository->makeUpdate($id, $request->validated());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->validated())]);
            return Common::success('Mise à jour effectuée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        $message = 'Suppression d\'une requête';
        try {
            $recup  = $this->requeteRepository->get($id);
            $result = $this->requeteRepository->makeDestroy($id);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($recup)]);
            return Common::successDelete('Requête supprimée avec succès', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function search(Request $request)
    {
        $message = 'Recherche de requêtes';
        try {
            $result = $this->requeteRepository->search($request->term);
            $this->ls->trace(['action_name' => $message, 'description' => $request->term]);
            return Common::success('Recherche effectuée', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    public function changeState($id, $state)
    {
        $message = 'Changement d\'état d\'une requête';
        try {
            $result = $this->requeteRepository->setStatus($id, $state);
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($result)]);
            return Common::success('État mis à jour', $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTHODES CONSERVÉES POUR RÉTROCOMPATIBILITÉ
    // ─────────────────────────────────────────────────────────────────────────

    /** @deprecated Utiliser getBanette() */
    public function getByPrestation(Request $request, $slug)
    {
        return $this->getBanette($slug);
    }

    /** @deprecated Utiliser index() avec filtre prestation_code */
    public function getByPrestationAll(Request $request, $slug)
    {
        $message = 'Récupération de toutes les requêtes d\'une prestation';
        try {
            $result = $this->requeteRepository->getByPrestationAll(['code' => $slug]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    /** @deprecated Utiliser traiter() avec decision=valider|rejeter */
    public function storeResponse(Request $request)
    {
        $message = 'Enregistrement de la réponse';
        try {
            $result = $this->requeteRepository->storeResponse($request->all());
            $this->ls->trace(['action_name' => $message, 'description' => json_encode($request->all())]);
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            $this->ls->trace(['action_name' => $message, 'description' => $th->getMessage()]);
            return Common::error($th->getMessage(), []);
        }
    }

    /** @deprecated Utiliser getBanette() */
    public function getByPrestationTreated(Request $request)
    {
        $message = 'Récupération des requêtes traitées';
        try {
            $result = $this->requeteRepository->getByPrestationTreated($request->all());
            return Common::success($message, $result);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }
}