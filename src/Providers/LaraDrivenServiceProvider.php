<?php

namespace Larakeeps\LaraDriven\Providers;

use Larakeeps\LaraDriven\Commands\createDomainDrivenDesignStructure;
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

        $this->mergeConfigDDD();

        $this->createFileYamlIfDoesntExists();

        if(!$this->checkExistStructureCreated()) return;

        $this->loadFileConfigsDdd();

        foreach ($this->structures as $path) {

            $this->loadModules($path);

        }

    }


    public function mergeConfigDDD()
    {
        $this->mergeConfigFrom(__DIR__."/../Config/ddd-config.php", 'ddd-config');
        $this->publishes([
            __DIR__."/../Config/ddd-config.php" => config_path('ddd-config.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands(createDomainDrivenDesignStructure::class);
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
        if(!config("ddd-config.load-migrations"))
            return $this;

        $this->loadMigrationsFrom($this->file->glob("{$this->path}/Database/Migrations/*"));

        return $this;
    }

    public function loadRoutes(): static
    {
        if(!config("ddd-config.load-routes")) return $this;

        foreach ($this->file->glob("{$this->path}/Routes/*") as $route) {

            $this->loadRoutesFrom($route);

        }

        return $this;
    }

    public function loadConfigs(): static
    {
        if(!config("ddd-config.load-configs"))  return $this;

        foreach ($this->file->glob("{$this->path}/Config/*") as $config) {

            $this->mergeConfigFrom($config, $this->file->name($config));

        }

        return $this;
    }

    public function loadTranslations(): static
    {
        if(!config("ddd-config.load-translations")) return $this;

        foreach ($this->file->glob("{$this->path}/Lang/*") as $lang) {

            $this->loadTranslationsFrom($lang, $this->file->name($lang));

        }

        return $this;
    }

    public function loadCommands(): static
    {
        if(!config("ddd-config.load-commands")) return $this;

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
