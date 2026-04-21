<?php
namespace App\Services;
use Log;
use Illuminate\Support\Facades\Http; // ✅ Ajouter cette ligne


class PNSService{

    public $header;
    public $payload;
    public $uxpClient="BJ/GOV/PNS/PRE-PROD-PORTAIL";
    public $uxpService="BJ/GOV/PNS/PRE-PROD-PORTAIL/rest-api-listener/v1";

    public function __construct($header,$payload) {
        $this->header = array_merge($header,['uxp-client' =>$this->uxpClient,'uxp-service' =>$this->uxpService]);
        $this->payload = $payload;
    }


    function reply() {

        try {
                info('Envoi de la décision PNS', [
                    'header' => $this->header,
                    'payload' => $this->payload
                ]);
                $response = Http::withHeaders($this->header)->post(env('PNS_URI').'/api/portal/event/uxp/rest',$this->payload);
                 if ($response?->successful()) {
                    return $response?->json();
                } else {
                    Log::error('Erreur lors de l\'envoi de la décision PNS', [
                        'status' => $response?->status(),
                         'response' => $response?->body()
                    ]);
                    return false;
                }

          
        } catch (\Throwable $th) {
            Log::error('Exception lors de l\'appel PNS', [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
        }finally {
            return null;
        }
    }


}

