<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class RepositoryAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $repository = null;
    protected string|null $path = null;
    private bool $implementsInterface = false;

    public function __construct(string $repository, $path)
    {
        $this->repository = $repository;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($repository, 'Repository.stub');

        $this->designService->setDefaultPath(app_path("{$path}"));

        $this->path = $path;

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

    protected function getFile($postfix = "Repository"): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase()."{$postfix}.php";
    }

    protected function repositoryExists($postfix = "*"): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase()."{$postfix}.php");
    }
    public function build(): bool|string
    {
//        if($this->repositoryExists())
//            return "Repository already exist.";

        if(!$this->designService->getStub())
            return "Repository stub not found.";

        $stub = $this->designService->setDataInStub( "{{repository}}",$this->designService->getFileNameStudlyCase());

        if(!$stub)
            return "Variable repository not found in stub.";

        $stub = $this->setModelAndRepository($stub);

        $stub = $this->setInterface($stub);

        if($this->file->put($this->getFile(), $stub) !== false) {

            if($this->implementsInterface)
               return $this->createInterface();

            return "Repository created successfully.";
        }

        return "Failed to create repository.";
    }


    protected function setModelAndRepository($stub): array|string
    {
        return  str_replace(
            [
                "{{Repository}}",
                "{{modelNameSpace}}",
                "{{model}}",
            ],
            [
                $this->designService->setCustomNameSpace("Repositories"),
                $this->designService->setCustomNameSpace("Models\\"
                    .$this->designService->setFileNameStudlyCase($this->repository)),
                $this->designService->setFileNameStudlyCase($this->repository)
            ], $stub);
    }

    protected function setInterface($stub): array|string
    {
        if($this->implementsInterface) {
            $namespace = "use App\\$this->repository\\Contracts\\{$this->repository}RepositoryInterface;";
            $implements = "implements {$this->designService->setFileNameStudlyCase($this->repository)}RepositoryInterface";
        }

        return str_replace(
            [ "{{interface}}", '{{interfaceNameSpace}}' ],
            [  $implements ?? '', $namespace ?? '' ], $stub);
    }

    protected function createInterface(): bool|string
    {
        $this->designService->setPath(app_path("{$this->path}/Contracts"));

        $this->designService->setStubFileName('RepositoryInterface.stub');

        if($this->repositoryExists('RepositoryInterface'))
            return "The repository was created, however the interface was not, as it already exists.";

        if(!$this->designService->getStub())
            return "RepositoryInterface stub not found.";

        $stub = $this->designService->setDataInStub( "{{interface}}",$this->designService->getFileNameStudlyCase());

        if($this->file->put($this->getFile('RepositoryInterface'), $stub) !== false)
            return 'Repository and Interface created successfully.';

        return "Failed to create interface.";
    }
}
