<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;

class MigrationAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $table = null;
    protected string|null $path = null;

    public function __construct(string $table, $path)
    {
        $this->table = $table;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($table, 'Migration.stub');

        $this->designService->setDefaultPath(app_path("{$path}/Database/Migrations"));
        $this->designService->setPathSource(app_path("{$path}"));

    }

    public function setPath(string $path): static
    {
        $this->designService->setPath($path);

        return $this;
    }
    protected function getFile(): string
    {
        return $this->designService->getPath().'/'.$this->designService->getDatePrefix()
            .'_create_'.$this->designService->getFileNamePlural().'_table.php';
    }

    protected function migrationExists(): bool
    {
        return $this->designService->FileExists('*_*_*_*_create_'
            .$this->designService->getFileNamePlural().'_table.php');
    }
    public function build(): bool|string
    {
        if($this->migrationExists())
            return "Migration already exist.";

        if(!$this->designService->getStub())
            return "Migration stub not found.";

        if(!$stub = $this->designService->setDataInStub("{{table}}", $this->designService->getFileNamePlural()))
            return "Variable table not found in stub.";

        if($this->file->put($this->getFile(), $stub) !== false)
            return "Migration created successfully.";

        return "Failed to create migration.";
    }
}
