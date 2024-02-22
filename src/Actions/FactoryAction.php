<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class FactoryAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $factory = null;
    protected string|null $path = null;

    public function __construct(string $factory, $path)
    {
        $this->factory = $factory;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($factory, 'Factory.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Database/Factories"));
        $this->designService->setPathSource(app_path("{$path}"));

    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase().'Factory.php';
    }

    protected function factoryExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->factoryExists())
            return "Factory already exist.";

        if(!$this->designService->getStub())
            return "Factory stub not found.";

        if(!$stub = $this->designService->setDataInStub(
            [
                "{{factory}}",
                "{{modelNameSpace}}",

            ], [
                $this->designService->getFileNameStudlyCase(),
                $this->designService->setCustomNameSpace("Models\\"
                .$this->designService->setFileNameStudlyCase($this->factory)),
            ]))
            return "Variable factory not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Factory created successfully.";

        return "Failed to create factory.";
    }
}
