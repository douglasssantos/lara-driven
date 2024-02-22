<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class SeedAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $seeder = null;
    protected string|null $path = null;

    public function __construct(string $seeder, $path)
    {
        $this->seeder = $seeder;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($seeder, 'Seed.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Database/Seeders"));
        $this->designService->setPathSource(app_path("{$path}"));

    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase().'Seeder.php';
    }

    protected function seederExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->seederExists())
            return "Seeder already exist.";

        if(!$this->designService->getStub())
            return "Seeder stub not found.";

        if(!$stub = $this->designService->setDataInStub(
            [
                "{{seeder}}",
                "{{modelNameSpace}}",

            ], [
                $this->designService->getFileNameStudlyCase(),
                $this->designService->setCustomNameSpace("Models\\"
                .$this->designService->setFileNameStudlyCase($this->seeder)),
            ]))
            return "Variable seeder not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Seeder created successfully.";

        return "Failed to create seeder.";
    }
}
