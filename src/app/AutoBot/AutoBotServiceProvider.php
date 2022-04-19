<?php

namespace App\AutoBot;

use Illuminate\Support\ServiceProvider;

class AutoBotServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/autobot.php' => config_path('autobot.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/autobot.php', 'autobot'
        );

        $this->app->bind('bot', AutoBot::class);
    }
}
