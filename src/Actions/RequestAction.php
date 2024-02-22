<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class RequestAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $request = null;
    protected string|null $path = null;

    public function __construct(string $request, $path)
    {
        $this->request = $request;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($request, 'Request.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Http/Requests"));
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

    protected function requestExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->requestExists())
            return "Request already exist.";

        if(!$this->designService->getStub())
            return "Request stub not found.";

        if(!$stub = $this->designService->setDataInStub("{{request}}", $this->designService->getFileNameStudlyCase()))
            return "Variable request not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Request created successfully.";

        return "Failed to create request.";
    }
}
