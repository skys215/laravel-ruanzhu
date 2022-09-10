<?php

namespace Ruanzhu\Generators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RuanzhuDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ruanzhu:doc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate docs for software copyrighti, including code and manual.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('ruanzhu:code');
        Artisan::call('ruanzhu:manual');
        Artisan::call('ruanzhu:env');
    }
}