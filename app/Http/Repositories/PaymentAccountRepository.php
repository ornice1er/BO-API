<?php

namespace App\Http\Repositories;

use App\Models\PaymentAccount;

class PaymentAccountRepository
{
    public function getAll()
    {
        return PaymentAccount::orderBy('name')->get();
    }

    public function get($id)
    {
        return PaymentAccount::findOrFail($id);
    }

    public function store(array $data): PaymentAccount
    {
        return PaymentAccount::create($data);
    }

    public function update($id, array $data): PaymentAccount
    {
        $account = PaymentAccount::findOrFail($id);
        $account->update($data);
        return $account;
    }

    public function destroy($id): bool
    {
        return PaymentAccount::findOrFail($id)->delete();
    }

    public function setStatus($id, $status): bool
    {
        return PaymentAccount::findOrFail($id)->update(['is_active' => $status]);
    }
}
