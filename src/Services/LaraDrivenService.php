<?php

namespace Larakeeps\LaraDriven\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class LaraDrivenService
{

    protected Filesystem $file;
    protected string|null $fileName = null;
    protected string|null $path = null;
    protected string|null $dirName = null;
    protected string|null $stubFileName;
    protected string|null $namespace;

    protected string $fileStructureYaml = 'containers.yaml';

    public function __construct(string $fileName = null, $stubFileName = null, $path = null)
    {
        $this->fileStructureYaml = config("lara-driven-config.containerFilePath", $this->fileStructureYaml);

        $this->fileName = $fileName;
        $this->stubFileName = $stubFileName;
        $this->dirName = $path;

        $this->file = new Filesystem();

    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function setStubFileName(string $stubFileName): static
    {
        $this->stubFileName = $stubFileName;

        return $this;
    }

    public function setNameSpace(string $namespace): static
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getNameSpace(): string|null
    {
        $nameSpace = Str::after($this->getPath(), "app/");

        $nameSpace = Str::replace("/", "\\", $nameSpace);

        $treatmentNameSpace = Str::headline("App\\{$nameSpace}");

        $this->setNameSpace(Str::remove(' ', $treatmentNameSpace));

        return $this->namespace;
    }

    public function setCustomNameSpace($namespace): string
    {

        $currentNameSpace = $this->getNameSpace();

        $currentNameSpace = Str::remove("App\\", $currentNameSpace);

        $path = explode("\\", $currentNameSpace);

        return "App\\{$path[0]}\\{$namespace}";

    }

    public function setDefaultPath($defaultPath = null): static
    {
        $this->path = $this->path ?? $defaultPath;

        return $this;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        $this->file->ensureDirectoryExists($this->path);

        return $this->path;
    }

    public function getDirname(): string
    {
        return $this->path;
    }

    public function getFileNameStudlyCase(): string
    {
        return Str::studly(Str::snake(Str::singular($this->fileName)));
    }

    public function setFileNameStudlyCase($fileName): string
    {
        return Str::studly(Str::snake(Str::singular($fileName)));
    }

    public function getFileNamePlural(): string
    {
        return Str::plural(Str::snake($this->fileName));
    }
    public function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }
    public function setStub(string $stubFileName): static
    {
        $this->stubFileName = $stubFileName;

        return $this;
    }
    public function getStub(): bool|string
    {
        $path = __DIR__."/../Stubs/{$this->stubFileName}";

        if(!$this->file->exists($path))
            return false;

        return $this->file->get($path, true);

    }

    public function checkExistVariableInStub(array|string $search, $stub): bool
    {
        if(is_string($search))
            return str_contains($stub, $search);

        $variablesFounded = true;

        foreach ($search as $var){

            if(!str_contains($stub, $var)){
                $variablesFounded = false;
                break;
            }

        }

        return $variablesFounded;
    }

    public function setDataInStub(array|string $search, array|string $replace): array|bool|string
    {
        $stub = $this->getStub();

        if(!$this->checkExistVariableInStub($search, $stub))
            return false;

        $stub = str_replace("{{namespace}}", $this->getNameSpace(), $stub);
        $stub = str_replace("{{nameservice}}", $this->setCustomNameSpace(""), $stub);

        return str_replace($search, $replace, $stub);

    }

    public function FileExists($pattern): bool
    {
        return count($this->file->glob( join_paths($this->path, $pattern) )) !== 0;
    }

    public function hasStructure($domain): bool|string
    {
        if(!$this->file->exists($this->fileStructureYaml))
            return "The ddd structure file does not exist.";

        $yaml = Yaml::parseFile($this->fileStructureYaml)['containers'] ?? false;

        if(!$yaml)
            return false;

        $containers = array_keys(Yaml::parseFile($this->fileStructureYaml)['containers']);

        return in_array(strtolower($domain), array_map("strtolower",$containers));
    }

    public function addStructureInDDD($domain, $path): string
    {
        if(!$this->file->exists($this->fileStructureYaml))
            return "The ddd structure file does not exist.";

        $yaml = Yaml::parseFile($this->fileStructureYaml);

        $containers = array_merge_recursive($yaml, ['containers' => [Str::kebab($domain) => $path]]);

        $containers = array_filter($containers['containers'], fn($container) => !empty($container));

        if($this->file->put($this->fileStructureYaml, Yaml::dump(['containers' => $containers])))
            return "The domain driven design structure was successfully created!";

        return "Failed to create the domain driven design structure!";

    }

}
