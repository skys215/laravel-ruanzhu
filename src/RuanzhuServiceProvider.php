<?php

namespace Ruanzhu;

use Illuminate\Support\ServiceProvider;
use Ruanzhu\Generators\RuanzhuCode;
use Ruanzhu\Generators\RuanzhuDoc;
use Ruanzhu\Generators\RuanzhuManual;

class RuanzhuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register commands
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            RuanzhuDoc::class,
            RuanzhuCode::class,
            RuanzhuManual::class,
        ]);
    }
}

