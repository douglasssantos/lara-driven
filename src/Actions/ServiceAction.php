<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class ServiceAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $service = null;
    protected string|null $path = null;
    private bool $implementsInterface = false;

    public function __construct(string $service, $path)
    {
        $this->service = $service;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($service, 'ServiceWithoutRepository.stub');

        $this->designService->setDefaultPath(app_path("{$path}"));

        $this->path = $path;

    }

    public function withRespository(): static
    {
        $this->designService->setStubFileName('ServiceWithRepository.stub');

        return $this;
    }

    public function empty(): static
    {
        $this->designService->setStubFileName('ServiceEmpty.stub');

        return $this;
    }

    public function withInterface(): static
    {
        $this->implementsInterface = true;

        return $this;
    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    protected function getFile($postfix = "Service"): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase()."{$postfix}.php";
    }

    protected function serviceExists($postfix = "*"): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase()."{$postfix}.php");
    }
    public function build(): bool|string
    {
//        if($this->serviceExists())
//            return "Service already exist.";

        if(!$this->designService->getStub())
            return "Service stub not found.";

        $stub = $this->designService->setDataInStub( "{{service}}",$this->designService->getFileNameStudlyCase());

        if(!$stub)
            return "Variable service not found in stub.";

        $stub = $this->setModelAndRepository($stub);

        $stub = $this->setInterface($stub);

        if($this->file->put($this->getFile(), $stub) !== false) {

            if($this->implementsInterface)
               return $this->createInterface();

            return "Service created successfully.";
        }

        return "Failed to create service.";
    }


    protected function setModelAndRepository($stub): array|string
    {
        return  str_replace(
            [
                "{{repositoryNameSpace}}",
                "{{modelNameSpace}}",
                "{{model}}",
            ],
            [
                $this->designService->setCustomNameSpace($this->designService->setFileNameStudlyCase($this->service)),
                $this->designService->setCustomNameSpace("Models\\"
                    .$this->designService->setFileNameStudlyCase($this->service)),
                $this->designService->setFileNameStudlyCase($this->service)
            ], $stub);
    }

    protected function setInterface($stub): array|string
    {
        if($this->implementsInterface) {
            $namespace = "use App\\$this->service\\Contracts\\{$this->service}Interface;";
            $implements = "implements {$this->designService->setFileNameStudlyCase($this->service)}Interface";
        }

        return str_replace(
            [ "{{interface}}", '{{interfaceNameSpace}}' ],
            [  $implements ?? '', $namespace ?? '' ], $stub);
    }

    protected function createInterface(): bool|string
    {
        $this->designService->setPath(app_path("{$this->path}/Contracts"));

        $this->designService->setStubFileName('ServiceInterface.stub');

        if($this->serviceExists('Interface'))
            return "The service was created, however the interface was not, as it already exists.";

        if(!$this->designService->getStub())
            return "ServiceInterface stub not found.";

        $stub = $this->designService->setDataInStub( "{{interface}}",$this->designService->getFileNameStudlyCase());

        if($this->file->put($this->getFile('Interface'), $stub) !== false)
            return 'Service and Interface created successfully.';

        return "Failed to create interface.";
    }
}
