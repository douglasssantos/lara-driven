<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class ControllerAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $controller = null;
    protected string|null $path = null;

    public function __construct(string $controller, $path)
    {
        $this->controller = $controller;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($controller, 'Controller.stub');

        $this->designService->setDefaultPath(app_path("{$path}"));
        $this->designService->setPathSource(app_path("{$path}"));

    }

    public function withoutService(): static
    {
        $this->designService->setStubFileName('ControllerEmpty.stub');

        return $this;
    }

    public function withServiceEmpty(): static
    {
        $this->designService->setStubFileName('ControllerWithServiceEmpty.stub');

        return $this;
    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase().'Controller.php';
    }

    protected function controllerExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'Controller.php');
    }
    public function build(): bool|string
    {
        if($this->controllerExists())
            return "Controller already exist.";

        if(!$this->designService->getStub())
            return "Controller stub not found.";

        if(!$stub = $this->designService->setDataInStub("{{controller}}", $this->designService->getFileNameStudlyCase()))
            return "Variable controller not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Controller created successfully.";

        return "Failed to create controller.";
    }
}
