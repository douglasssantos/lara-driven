<?php

namespace Larakeeps\LaraDriven\Services;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class SeederService extends Seeder
{
    protected $structures = [];
    protected string $fileStructureYaml = 'containers.yaml';
    protected string $path = '';

    protected Filesystem $file;


    public function run(): void
    {


        $this->file = new Filesystem();

        $this->fileStructureYaml = config("lara-driven-config.containerFilePath", $this->fileStructureYaml);

        if(!$this->checkExistStructureCreated()) return;

        $this->loadFileConfigsDdd();

        foreach ($this->structures as $path) {

            foreach ($this->file->glob("{$path}/Database/Seeders/*") as $seeder) {

//                $this->call();

                dd($seeder);

            }

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
}
