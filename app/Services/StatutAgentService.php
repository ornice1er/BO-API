<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StatutAgentService
{
    public function getStatutAgent($id)
    {

        try {
            info($id);

            return Http::withHeaders(['Authorization' => request()->header('Authorization')])
                ->get(env('APP_ACTIVITY_URL').'/api/statut-agents/'.$id.'?trace=0')?->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
