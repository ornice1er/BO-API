<?php

namespace App\Jobs;

use App\Services\TwilioService;
use App\Utilities\Mailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $twService;

    protected $data;

    protected $name;

    protected $email;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $name, $email)
    {
        $this->twService = new TwilioService;
        $this->data = $data;
        $this->name = $name;
        $this->email = $email;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mailer::sendSimple('emails.notification', $this->data, 'Notification FCT', $this->name, $this->email);

    }
}
