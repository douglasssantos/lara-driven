<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class CommandAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $command = null;
    protected string|null $path = null;

    public function __construct(string $command, $path)
    {
        $this->command = $command;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($command, 'Command.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Commands"));
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

    protected function commandExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->commandExists())
            return "Command already exist.";

        if(!$this->designService->getStub())
            return "Command stub not found.";

        if(!$stub = $this->designService->setDataInStub(
            [
                "{{command}}",
                "{{commandSlug}}",
            ],
            [
                $this->designService->getFileNameStudlyCase(),
                Str::snake($this->designService->getFileNameStudlyCase(), '-'),
            ]))
            return "Variable command not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Command created successfully.";

        return "Failed to create command.";
    }
}
