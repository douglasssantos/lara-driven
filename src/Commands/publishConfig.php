<?php

namespace Larakeeps\LaraDriven\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class publishConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lara-driven:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for publish configs of lara-driven';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Artisan::call("vendor:publish --tag=lara-driven-config");

        echo Artisan::output();

        $this->optimize();

    }
    public function optimize()
    {
        Artisan::call("optimize:clear");

        echo Artisan::output();

        Artisan::call("optimize");

        echo Artisan::output();

    }
}
