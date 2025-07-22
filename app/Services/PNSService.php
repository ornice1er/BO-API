<?php
namespace App\Services;

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
                $response = Http::withHeaders($this->header)->post(env('PNS_URI').'/api/portal/event/uxp/rest',$this->payload);
                 if ($response->successful()) {
                    return $response->json();
                }

            Log::error('Erreur lors de l\'envoi de la décision PNS', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Throwable $th) {
            Log::error('Exception lors de l\'appel PNS', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
            return null;

    }


}

