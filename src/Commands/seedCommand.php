<?php

namespace Larakeeps\LaraDriven\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Filesystem\Filesystem;
use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Symfony\Component\Yaml\Yaml;

class seedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lara-driven:seed {--domain=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to run domain seeds.';

    protected $structures = [];
    protected string $fileStructureYaml = 'containers.yaml';
    protected string $path = '';

    protected Filesystem $file;
    protected LaraDrivenService $designService;

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->file = new Filesystem();
        $this->designService = new LaraDrivenService();

        $this->fileStructureYaml = config("lara-driven-config.containerFilePath", $this->fileStructureYaml);

        if(!$this->checkExistStructureCreated()) return;

        $this->output->title("Starting to populate the seed");

        $domains = $this->option("domain");

        $getDomainInOneOption = explode(",", $domains[0] ?? "");

        if(count($getDomainInOneOption) > 1){

            foreach ($domains as $domain) {
                if($domain != $domains[0])
                    $getDomainInOneOption[] = $domain;
            }

            $domains = $getDomainInOneOption;

        }

        if(count($domains) > 0)
            $domains = array_map("strtolower", $domains ?? []);

        $this->loadFileConfigsDdd();
        $progress = $this->output->createProgressBar(count($domains) ?? count($this->structures));

        $progress->start();

        foreach ($this->structures as $path) {

            $this->designService->setPath("{$path}/Database/Seeders");

            if(count($domains) > 0) {
                $this->runEspecificSeeder($domains, $path);
            }else{
                $this->runAllSeeders();
            }

            $progress->advance();

        }

        $progress->finish();
        $this->info("");
        $this->optimize();
        $this->info("");

    }

    public function runEspecificSeeder($domains, $path)
    {
        $path = strtolower($this->file->name($path));
        $runSeeder = false;

        foreach ($this->file->glob("{$this->designService->getPath()}/*") as $seeder) {

            foreach ($domains as $domain) {

                $domain = strtolower($domain);

                $runSeeder = $domain == $path;

                if(str_contains($domain, '@'))
                    $runSeeder = $domain == strtolower("{$path}@{$this->file->name($seeder)}");

                if($runSeeder) {
                    Artisan::call("db:seed", [
                        "class" => app("{$this->designService->getNameSpace()}\\{$this->file->name($seeder)}")::class
                    ]);
                }
            }


        }
    }


    public function runAllSeeders()
    {

        foreach ($this->file->glob("{$this->designService->getPath()}/*") as $seeder) {

            Artisan::call("db:seed", [
                "class" => app("{$this->designService->getNameSpace()}\\{$this->file->name($seeder)}")::class
            ]);

        }

    }


    public function loadYaml()
    {
        return Yaml::parseFile(base_path($this->fileStructureYaml));
    }

    public function loadFileConfigsDdd(): void
    {
        $this->structures = $this->loadYaml()['containers'];
    }

    public function checkExistStructureCreated(): bool
    {
        $yaml = $this->loadYaml();

        return is_countable($yaml['containers']) && count($yaml['containers']);

    }

    public function optimize()
    {

        Artisan::call("optimize:clear");

        echo Artisan::output();

        Artisan::call("optimize");

        echo Artisan::output();

    }
}
