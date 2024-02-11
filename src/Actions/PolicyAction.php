<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class PolicyAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $policy = null;
    protected string|null $path = null;

    public function __construct(string $policy, $path)
    {
        $this->policy = $policy;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($policy, 'Policy.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Models/Policies"));

    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }

    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->designService->getFileNameStudlyCase().'Policy.php';
    }

    protected function policyExists(): bool
    {
        return $this->designService->FileExists($this->designService->getFileNameStudlyCase().'*.php');
    }
    public function build(): bool|string
    {
        if($this->policyExists())
            return "Policy already exist.";

        if(!$this->designService->getStub())
            return "Policy stub not found.";

        if(!$stub = $this->designService->setDataInStub(
            [
                "{{policy}}",
                "{{modelNameSpace}}",
                "{{model}}",

            ],
            [
                $this->designService->getFileNameStudlyCase(),
                $this->designService->setCustomNameSpace("Models\\"
                .$this->designService->setFileNameStudlyCase($this->policy)),
                ", {$this->designService->setFileNameStudlyCase($this->policy)} \$"
                .Str::camel($this->designService->setFileNameStudlyCase($this->policy))
            ]))
            return "Variable policy not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Policy created successfully.";

        return "Failed to create policy.";
    }
}
