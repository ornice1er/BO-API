<?php

namespace App\Console\Commands;

use App\Jobs\NotificationMail;
use App\Jobs\NotificationSMS;
use App\Jobs\NotificationWhatsapp;
use Illuminate\Console\Command;

class NotificationSimulator extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fct:notification-simulator {--to=} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permet de simuler l\'envoi de notification par les trois cannaux';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = 'Exemple notification FCT';
        if ($this->option('to')) {
            NotificationSMS::dispatch($this->option('to'), $message)->onQueue('notifications');
            NotificationWhatsapp::dispatch($this->option('to'), $message)->onQueue('notifications');
        }
        if ($this->option('email')) {
            NotificationMail::dispatch(['data' => $message], 'Utilisateur SPFCT', $this->option('email'))->onQueue('notifications');
        }

    }
}
