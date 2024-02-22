<?php

namespace Larakeeps\LaraDriven\Commands;

use Larakeeps\LaraDriven\Actions\CommandAction;
use Larakeeps\LaraDriven\Actions\ConfigAction;
use Larakeeps\LaraDriven\Actions\ControllerAction;
use Larakeeps\LaraDriven\Actions\FactoryAction;
use Larakeeps\LaraDriven\Actions\MiddlewareAction;
use Larakeeps\LaraDriven\Actions\MigrationAction;
use Larakeeps\LaraDriven\Actions\ModelAction;
use Larakeeps\LaraDriven\Actions\PolicyAction;
use Larakeeps\LaraDriven\Actions\RepositoryAction;
use Larakeeps\LaraDriven\Actions\RequestAction;
use Larakeeps\LaraDriven\Actions\RouteAction;
use Larakeeps\LaraDriven\Actions\SeedAction;
use Larakeeps\LaraDriven\Actions\ServiceAction;
use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class createDomainDrivenDesignStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lara-driven:make {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create domaín drive design structure with all stack;';

    protected array $attributes = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(!empty($this->argument('domain')))
            $this->attributes['domain'] = $this->argument('domain');

        $this->runQuestion();
    }

    public function runQuestion()
    {

        $this->designService = new LaraDrivenService;

        if(empty($this->argument('domain')))
            $this->attributes['domain'] = $this->ask("Enter your domain name");

        if(empty($this->attributes['domain'])) {

            $this->error("Cannot create a domain with an empty name!");

            return false;

        }

        if(preg_match('/^[^ ].* .*[^ ]$/', $this->attributes['domain'])
            || preg_match('/[\'^£$%&*()}{@#~?>.<>,;|=_+¬-]/', $this->attributes['domain'])){

            $this->error("Cannot create a domain with spaces or special characters");

            return false;

        }

        if($this->designService->hasStructure($this->attributes['domain'])){

            $this->error("The domain you entered already exists!");

            return false;

        }

        if($this->designService->hasStructure($this->attributes['domain'])){

            $this->error("The domain you entered already exists!");

            return false;

        }

        $keepPathName = $this->confirm(
            "Do you want to keep the domain name as the folder name? {$this->__($this->attributes['domain'])}",
            true);

        $this->attributes['path'] = $keepPathName ? $this->attributes['domain'] : $this->ask("Enter the name for your domain folder");

        $this->installModel();
        $this->installPolicy();
        $this->installService();
        $this->installController();
        $this->installRoutes();
        $this->installConfig();
        $this->installCommand();

        $this->optimize();

        $this->designService->addStructureInDDD($this->attributes['domain'], "app/{$this->attributes['path']}");

    }

    public function optimize()
    {
        Artisan::call("optimize:clear");

        echo Artisan::output();

        Artisan::call("optimize");

        echo Artisan::output();

    }

    public function setChoice($question, array $choices, $default = null, $attempts = null, $multiple = false): string
    {
        return Str::lower($this->choice($question, $choices, $default, $attempts, $multiple));
    }

    public function setConfirm($question): bool
    {
        return Str::lower($this->choice($question, ['No', "Yes"], 'Yes')) === 'yes';
    }

    public function __($text): string
    {
        return "<fg=white>[</><fg=blue>$text</><fg=white>]</>";
    }

    public function builder()
    {
        $this->line((new CommandAction($this->argument('structure'), $this->argument('structure')))->build());
        $this->line((new ConfigAction($this->argument('structure'), $this->argument('structure')))->build());
    }

    public function installModel(): void
    {
        if(config("lara-driven-config.create-model")) {
            $this->attributes['model']['install'] = $this->confirm("Do you want to create a {$this->__('Model')} for the domain?", true);

            if ($this->attributes['model']['install'])
                (new ModelAction($this->attributes['domain'], $this->attributes['path']))->build();

            $this->installDatabase();
        }
    }

    public function installPolicy(): void
    {
        if(config("lara-driven-config.create-policy")) {
            $this->attributes['model']['installPolicy'] = $this->confirm("Do you want to create a {$this->__('Policy')} for your model?");

            if ($this->attributes['model']['installPolicy'])
                (new PolicyAction($this->attributes['domain'], $this->attributes['path']))->build();
        }
    }

    public function installDatabase(): void
    {
        if(!config("lara-driven-config.create-migration")
            && !config("lara-driven-config.create-seed")
            && !config("lara-driven-config.create-factory"))  return;

        $this->attributes['database']['install'] = $this->confirm(
            "Do you want to create {$this->__('Migration')}, {$this->__('Seed')} or {$this->__('Factory')}?", true);

        if($this->attributes['database']['install']){

            $this->attributes['database']['which'] = $this->choice(
                "Select one or more classes to manipulate your database.", $this->choiceInstallDatabase(), 0, null, true);

            if(in_array('Migration', $this->attributes['database']['which']))
                $this->installMigration(false);

            if(in_array('Seed', $this->attributes['database']['which']))
                $this->installSeed(false);

            if(in_array('Factory', $this->attributes['database']['which']))
                $this->installFactory(false);

            if(in_array('All', $this->attributes['database']['which'])) {

                $this->installMigration(false);
                $this->installSeed(false);
                $this->installFactory(false);

            }

        }

    }

    public function choiceInstallDatabase(): array
    {
        $database = [];

        if(config("lara-driven-config.create-migration"))
            $database[] = 'Migration';

        if(config("lara-driven-config.create-migration"))
            $database[] = 'Seed';

        if(config("lara-driven-config.create-migration"))
            $database[] = 'Factory';

        if(is_countable($database) && count($database) > 1)
            $database[] = 'All';

        return $database;

    }

    public function installMigration($showQuestion = true): void
    {
        if(!config("lara-driven-config.create-migration"))  return;

        if($showQuestion)
            $this->attributes['database']['install'] = $this->confirm("Do you want to create {$this->__('Migration')} for your model?");

        if(!$showQuestion || ($this->attributes['database']['install'] ?? false))
            (new MigrationAction($this->attributes['domain'], $this->attributes['path']))->build();
    }

    public function installSeed($showQuestion = true): void
    {
        if(!config("lara-driven-config.create-seed"))  return;

        if($showQuestion)
            $this->attributes['database']['install'] = $this->confirm("Do you want to create a {$this->__('Seed')} to insert default values into your database?");

        if(!$showQuestion || ($this->attributes['database']['install'] ?? false))
            (new SeedAction($this->attributes['domain'], $this->attributes['path']))->build();
    }

    public function installFactory($showQuestion = true): void
    {
        if(!config("lara-driven-config.create-factory"))  return;

        if($showQuestion)
            $this->attributes['database']['install'] = $this->confirm("Do you want to create a {$this->__('Factory')} to populate your database?");

        if(!$showQuestion || ($this->attributes['database']['install'] ?? false))
            (new FactoryAction($this->attributes['domain'], $this->attributes['path']))->build();
    }

    public function installService(): void
    {
        if(config("lara-driven-config.create-service")) {

            $this->attributes['service']['installEmpty'] = $this->confirm(
                "Do you want to create an empty {$this->__('Service')}?");

            if (!$this->attributes['service']['installEmpty']) {

                $this->attributes['service']['installInterface'] = $this->confirm(
                    "Do you want to create an {$this->__('Interface')} for your service?");

                if ($this->attributes['model']['install'])
                    $this->installRepository();

            }

            $service = (new ServiceAction($this->attributes['domain'], $this->attributes['path']));

            if ($this->attributes['repository']['install'] ?? false) $service->withRespository();
            if ($this->attributes['service']['installInterface'] ?? false) $service->withInterface();
            if ($this->attributes['service']['installEmpty']) $service->empty();

            $service->build();
        }

    }

    public function installRepository(): void
    {

        if($this->attributes['model']['install']) {

            $this->attributes['repository']['install'] = $this->confirm(
                "Do you want to create the {$this->__('Repository')} to separate model actions from your service?", true);

            if($this->attributes['repository']['install'])
                $this->attributes['repository']['installInterface'] = $this->confirm(
                "Do you want to create an {$this->__('Interface')} for your repository?");

        }

        if($this->attributes['repository']['install']) {

            $repository = (new RepositoryAction($this->attributes['domain'], $this->attributes['path']));

            if ($this->attributes['repository']['installInterface'] ?? false) $repository->withInterface();

            $repository->build();
        }

    }
    public function installController($showQuestion = true): void
    {
        if(config("lara-driven-config.create-controller")) {
            if ($showQuestion)
                $this->attributes['controller']['install'] = $this->confirm("Do you want to create a {$this->__('Controller')} for your domain?", true);

            if (!$showQuestion || ($this->attributes['controller']['install'] ?? false))
                (new ControllerAction($this->attributes['domain'], $this->attributes['path']))->build();

            $this->installRequest();
        }

    }
    public function installRequest($showQuestion = true): void
    {
        if(config("lara-driven-config.create-request")) {
            if ($showQuestion)
                $this->attributes['controller']['installRequest'] = $this->confirm(
                    "Do you want to create a {$this->__('Request')} for processing and validation of your controller?", true);

            if (!$showQuestion || ($this->attributes['controller']['installRequest'] ?? false))
                (new RequestAction($this->attributes['domain'], $this->attributes['path']))->build();
        }

    }
    public function installMiddleware($showQuestion = true): void
    {
        if(config("lara-driven-config.create-middleware")) {
            if ($showQuestion)
                $this->attributes['controller']['installMiddleware'] = $this->confirm("Do you want to add {$this->__('Middleware')} to your routes?", true);

            if (!$showQuestion || ($this->attributes['controller']['installMiddleware'] ?? false))
                (new MiddlewareAction($this->attributes['domain'], $this->attributes['path']))->build();
        }
    }

    public function installRoutes($showQuestion = true): void
    {
        if(config("lara-driven-config.create-route")) {

            if($showQuestion) {
                $install = $this->confirm("Do you want to install {$this->__('Routes')} on your domain?", true);
                $which = $this->setChoice("Which routes do you want to install?", ['Web', "Api", "Both"], 'Web');

                if ($this->attributes['controller']['install'] ?? false)
                    $controller = $this->confirm("Do you want to assign the routes to the {$this->__('Controller')}?", true);
            }else{
                $install = true;
                $controller = true;
                $which = 'both';
            }

            if ($install) {

                $this->installMiddleware();

                $route = (new RouteAction($this->attributes['domain'], $this->attributes['path']));

                if ($which === "web" || $which === "both") $route->isWeb();

                if ($which === "api" || $which === "both") $route->isApi();

                if (config("lara-driven-config.create-controller"))
                    if ($controller ?? false) $route->withController();

                if (config("lara-driven-config.create-middleware"))
                    if ($this->attributes['controller']['installMiddleware'] ?? false) $route->withMiddleware();

                $route->build();

            }
        }
    }

    public function installConfig($showQuestion = true): void
    {
        if(config("lara-driven-config.create-config")) {
            if ($showQuestion)
                $this->attributes['config']['install'] = $this->confirm("Do you want to create the {$this->__('Config')} file on your domain?", true);

            if (!$showQuestion || ($this->attributes['config']['install'] ?? false))
                (new ConfigAction($this->attributes['domain'], $this->attributes['path']))->build();
        }
    }

    public function installCommand(): void
    {
        if(config("lara-driven-config.create-command")) {
                $this->attributes['command']['install'] = $this->confirm("Do you want to create the {$this->__('Command')} file for your domain?", false);

            if ($this->attributes['command']['install'])
                (new CommandAction($this->attributes['domain'], $this->attributes['path']))->build();
        }
    }

}
