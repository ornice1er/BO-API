<?php

namespace App\Providers;

use App\Events\ChangeStatutAgentEvent;
use App\Listeners\ChangeStatutAgentListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            ChangeStatutAgentEvent::class,
            ChangeStatutAgentListener::class,
        );
        Schema::defaultStringLength(191);

        // Queue::connection('rabbitmq')->pushRaw(json_encode([
        //     'event' => 'TypeActivityDeleteEvent',
        //     'activity_id' => 123, // Remplace par la vraie donnée
        // ]));
        if (config('app.env') === 'production') {
                URL::forceScheme('https');
         }

    }
}