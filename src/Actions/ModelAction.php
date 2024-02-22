<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class ModelAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $model = null;
    protected string|null $path = null;

    public function __construct(string $model, $path)
    {
        $this->model = $model;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($model, 'Model.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Models"));
        $this->designService->setPathSource(app_path("{$path}"));

//        $this->designService->setNameSpace("App\\{$path}\\Models");

    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase().'.php';
    }

    protected function modelExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->modelExists())
            return "Model already exist.";

        if(!$this->designService->getStub())
            return "Model stub not found.";

        if(!$stub = $this->designService->setDataInStub("{{model}}", $this->designService->getFileNameStudlyCase()))
            return "Variable model not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Model created successfully.";

        return "Failed to create model.";
    }
}
