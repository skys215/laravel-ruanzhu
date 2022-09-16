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
    protected $description = 'Generate docs for software copyright, including code, env and manual.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('ruanzhu:code');
        $this->call('ruanzhu:manual');
        $this->call('ruanzhu:env');
    }
}