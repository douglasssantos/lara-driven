<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class MiddlewareAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $middleware = null;
    protected string|null $path = null;

    public function __construct(string $middleware, $path)
    {
        $this->middleware = $middleware;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($middleware, 'Middleware.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Http/Middleware"));
        $this->designService->setPathSource(app_path("{$path}"));

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

    protected function middlewareExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->middlewareExists())
            return "Middleware already exist.";

        if(!$this->designService->getStub())
            return "Middleware stub not found.";

        if(!$stub = $this->designService->setDataInStub("{{middleware}}", $this->designService->getFileNameStudlyCase()))
            return "Variable middleware not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Middleware created successfully.";

        return "Failed to create middleware.";
    }
}
