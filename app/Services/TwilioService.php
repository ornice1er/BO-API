<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;

    protected $from;

    protected $from2;

    public function __construct()
    {

        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        $this->from = env('TWILIO_PHONE_NUMBER');
        $this->from2 = env('TWILIO_WPHONE_NUMBER');
    }

    public function sendSms($to, $message)
    {

        try {

            $to = strlen($to) > 10 ? $to : '+229'.$to;

            return $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function sendWhatsappSms($to, $message, $code = null)
    {
        try {
            // $to = strlen($to) > 10 ? $to : '+229'.$to;
            info($code);
            $to = '+22961624402';

            return $this->client->messages->create('whatsapp:'.$to, [
                'from' => 'whatsapp:'.$this->from2,
                'contentSid' => 'HXe83cd5d90aa99a8f055bf4ffe7e24649',
                'contentVariables' => json_encode([
                    '1' => "$code",
                ]),
                'body' => $message,
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }

    }
}
