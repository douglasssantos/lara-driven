<?php

namespace Larakeeps\LaraDriven\Commands;

use Illuminate\Console\Command;
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
    protected $signature = 'lara-driven:seed {--domain=?}';

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

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $this->file = new Filesystem();

        $this->fileStructureYaml = config("lara-driven-config.containerFilePath", $this->fileStructureYaml);

        if(!$this->checkExistStructureCreated()) return;

        $this->output->title("Starting to populate the seed");

        $getDomain = $this->option("domain");

        $getDomainInOneOption = explode(",", $getDomain[0]);

        if(count($getDomainInOneOption) > 1 && count($getDomain) == 1){
            $getDomain = $getDomainInOneOption;
        }

        $getDomain = array_map("strtolower", $getDomain ?? []);

        $this->loadFileConfigsDdd();
        $progress = $this->output->createProgressBar(count($getDomain) ?? count($this->structures));

        $progress->start();

        foreach ($this->structures as $path) {

            $designService = new LaraDrivenService();

            if(count($getDomainInOneOption) > 0
                && !in_array(strtolower($this->file->name("{$path}/")), $getDomain))
                continue;

            $designService->setPath("{$path}/Database/Seeders");

            foreach ($this->file->glob("{$designService->getPath()}/*") as $seeder) {

                Artisan::call("db:seed", [
                    "class" => app("{$designService->getNameSpace()}\\{$this->file->name($seeder)}")::class
                ]);

            }

            $progress->advance();

        }

        $progress->finish();
        $this->info("");
        $this->optimize();
        $this->info("");

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
