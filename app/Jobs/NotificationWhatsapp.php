<?php

namespace App\Jobs;

use App\Services\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $twService;

    protected $to;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($to, $message)
    {
        $this->twService = new TwilioService;
        $this->to = $to;
        $this->message = $message;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $this->twService->sendWhatsappSms($this->to, $this->message);
    }
}
