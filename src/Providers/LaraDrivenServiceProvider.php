<?php

namespace Larakeeps\LaraDriven\Providers;

use Larakeeps\LaraDriven\Commands\createDomainDrivenDesignStructure;
use Larakeeps\LaraDriven\Commands\publishConfig;
use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Yaml\Yaml;
class LaraDrivenServiceProvider extends ServiceProvider
{

    protected $structures = [];
    protected string $fileStructureYaml = 'containers.yaml';
    protected string $path = '';

    protected Filesystem $file;

    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->file = new Filesystem();

        $this->fileStructureYaml = config("lara-driven-config.containerFilePath", $this->fileStructureYaml);

        $this->mergeConfigDDD();

        $this->createFileYamlIfDoesntExists();

        if(!$this->checkExistStructureCreated()) return;

        $this->loadFileConfigsDdd();

        foreach ($this->structures as $path) {

            $this->loadModules($path);

        }

    }

    public function mergeConfigDDD(): void
    {
        $this->mergeConfigFrom(__DIR__."/../../config/lara-driven-config.php", 'lara-driven-config');
        $this->publishes([
            __DIR__."/../../config/lara-driven-config.php" => config_path('lara-driven-config.php'),
        ], "lara-driven-config");

        if ($this->app->runningInConsole()) {
            $this->commands(createDomainDrivenDesignStructure::class);
            $this->commands(publishConfig::class);
        }
    }

    public function loadFileConfigsDdd(): void
    {
        $config = Yaml::parseFile(base_path($this->fileStructureYaml));
        $this->structures = $config['containers'];
    }
    public function createFileYamlIfDoesntExists(): void
    {
        if (!$this->file->exists(base_path($this->fileStructureYaml))) {

            $this->file->put(base_path($this->fileStructureYaml), "containers:\n");

        }

    }

    public function checkExistStructureCreated(): bool
    {
        $yaml = Yaml::parseFile(base_path($this->fileStructureYaml));

        return is_countable($yaml['containers']) && count($yaml['containers']);

    }

    public function loadModules($path): void
    {
        $this->path = $path;

        $this
            ->loadConfigs()
            ->loadTranslations()
            ->loadMigrations()
            ->loadRoutes()
            ->loadCommands();
    }

    public function loadMigrations(): static
    {
        if(!config("lara-driven-config.load-migrations"))
            return $this;

        $this->loadMigrationsFrom($this->file->glob("{$this->path}/Database/Migrations/*"));

        return $this;
    }

    public function loadRoutes(): static
    {
        if(!config("lara-driven-config.load-routes")) return $this;

        foreach ($this->file->glob("{$this->path}/Routes/*") as $route) {

            $this->loadRoutesFrom($route);

        }

        return $this;
    }

    public function loadConfigs(): static
    {
        if(!config("lara-driven-config.load-configs"))  return $this;

        foreach ($this->file->glob("{$this->path}/Config/*") as $config) {

            $this->mergeConfigFrom($config, $this->file->name($config));

        }

        return $this;
    }

    public function loadTranslations(): static
    {
        if(!config("lara-driven-config.load-translations")) return $this;

        foreach ($this->file->glob("{$this->path}/Lang/*") as $lang) {

            $this->loadTranslationsFrom($lang, $this->file->name($lang));

        }

        return $this;
    }

    public function loadCommands(): static
    {
        if(!config("lara-driven-config.load-commands")) return $this;

        $designService = new LaraDrivenService();

        $designService->setPath("{$this->path}/Commands");

        if ($this->app->runningInConsole()) {

            foreach ($this->file->glob("{$designService->getPath()}/*") as $command) {

                $this->commands(app("\\{$designService->getNameSpace()}\\{$this->file->name($command)}")::class);

            }
        }

        return $this;
    }

}
