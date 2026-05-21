<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PaymentAccountRepository;
use App\Utilities\Common;
use Illuminate\Http\Request;

class PaymentAccountController extends Controller
{
    protected PaymentAccountRepository $repo;

    public function __construct(PaymentAccountRepository $repo)
    {
        $this->repo = $repo;
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            return Common::success('Comptes de recette', $this->repo->getAll());
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function show($id)
    {
        try {
            return Common::success('Compte trouvé', $this->repo->get($id));
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'type'        => 'required|in:bjpay,fedapay,kkiapay',
            'api_key'     => 'nullable|string',
            'secret_key'  => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'env'         => 'nullable|in:test,production',
        ]);

        try {
            return Common::successCreate('Compte créé', $this->repo->store($data));
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:120',
            'type'        => 'sometimes|required|in:bjpay,fedapay,kkiapay',
            'api_key'     => 'nullable|string',
            'secret_key'  => 'nullable|string',
            'webhook_url' => 'nullable|url',
            'env'         => 'nullable|in:test,production',
        ]);

        try {
            return Common::success('Compte mis à jour', $this->repo->update($id, $data));
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function destroy($id)
    {
        try {
            $this->repo->destroy($id);
            return Common::successDelete('Compte supprimé', []);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }

    public function changeState($id, $state)
    {
        try {
            $this->repo->setStatus($id, $state);
            $msg = $state == 1 ? 'activé' : 'désactivé';
            return Common::success("Compte $msg", []);
        } catch (\Throwable $th) {
            return Common::error($th->getMessage(), []);
        }
    }
}
