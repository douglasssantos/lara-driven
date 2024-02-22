<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ConfigAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $config = null;
    protected string|null $path = null;

    public function __construct(string $config, $path)
    {
        $this->config = $config;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($config, 'Config.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Config"));
        $this->designService->setPathSource(app_path("{$path}"));

    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    public function getNameFile(): string
    {
        return Str::snake($this->designService->getFileNameStudlyCase());
    }

    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->getNameFile().'.php';
    }

    protected function configExists(): bool
    {
        return $this->designService->FileExists($this->getNameFile().'*.php');
    }
    public function build(): bool|string
    {
        if($this->configExists())
            return "Config already exist.";

        $stub = $this->designService->getStub();

        if(!$stub)
            return "Config stub not found.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Config created successfully.";

        return "Failed to create config.";
    }
}
