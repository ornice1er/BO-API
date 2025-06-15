<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EquipeService
{
    public function getEquipe($id)
    {

        try {
            return Http::withHeaders(['Authorization' => request()->header('Authorization')])
                ->get(env('APP_EDITION_URL').'/api/equipes-rh/'.$id.'?trace=0')?->json();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
