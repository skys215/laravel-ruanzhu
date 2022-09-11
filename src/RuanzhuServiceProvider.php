<?php

namespace Ruanzhu;

use Illuminate\Support\ServiceProvider;
use Ruanzhu\Generators\RuanzhuCode;
use Ruanzhu\Generators\RuanzhuEnv;
use Ruanzhu\Generators\RuanzhuDoc;
use Ruanzhu\Generators\RuanzhuManual;

class RuanzhuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config files
        if ($this->app->runningInConsole()) {
            $configPath = __DIR__.'/../config/ruanzhu.php';
            $this->publishes([
                $configPath => config_path('ruanzhu.php'),
            ], 'ruanzhu-config');

            // Register commands
            $this->commands([
                RuanzhuDoc::class,
                RuanzhuEnv::class,
                RuanzhuCode::class,
                RuanzhuManual::class,
            ]);

            return;
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ruanzhu.php', 'ruanzhu');
    }
}