<?php

namespace Larakeeps\LaraDriven\Actions;

use Larakeeps\LaraDriven\Services\LaraDrivenService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class RouteAction
{
    protected Filesystem $file;
    protected LaraDrivenService $designService;
    protected string|null $router = null;
    protected string|null $path = null;
    protected bool $isWeb = false;
    protected bool $isApi = false;
    protected bool $withController = false;
    protected bool $withMiddleware = false;

    public function __construct(string $router, $path)
    {
        $this->router = $router;

        $this->file = new Filesystem();

        $this->designService = new LaraDrivenService($router, 'Route.stub');

        $this->path = $path;

        $this->designService->setDefaultPath(app_path("{$path}/Routes"));
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

    public function isApi(): static
    {
        $this->isApi = true;

        return $this;
    }


    public function isWeb(): static
    {
        $this->isWeb = true;

        return $this;
    }

    public function withController(): static
    {
        $this->withController = true;

        return $this;
    }

    public function withMiddleware(): static
    {
        $this->withMiddleware = true;

        return $this;
    }
    protected function routerExists($router): bool
    {
        return $this->designService->FileExists("{$router}.php");
    }
    public function build(): bool|string
    {

        if(!$this->designService->getStub())
            return "Route stub not found.";

        if(!$stub = $this->designService->setDataInStub( "{{router}}",
            Str::snake($this->designService->getFileNameStudlyCase())))
            return "Variable router not found in stub.";

        $stub = $this->setController($stub);
        $stub = $this->setMiddleware($stub);

        $message = null;

        if($this->isWeb){

            if($this->routerExists('web')) {

                $message .= "Route Web already exist.\n";

            }elseif($this->file->put($this->designService->getPath().'/web.php', $stub) !== false) {

                $message .= "Route Web created successfully.";

            }

        }

        if($this->isApi){

            if($this->routerExists('api')) {

                $message .= "Route Api already exist.";

            }elseif($this->file->put($this->designService->getPath().'/api.php', $stub) !== false) {

                $message .= "Route Api created successfully.";

            }

        }

        if(!is_null($message)) return $message;

        return "Failed to create router.";
    }

    public function setController($stub): array|string
    {
        if($this->withController)
            $controller = $this->designService->setCustomNameSpace($this->designService->setFileNameStudlyCase($this->router));

        return str_replace(
            [
                "{{controllerNameSpace}}",
                "{{controller}}"
            ],
            [
                ( $controller ?? false ? "use {$controller}Controller;" : '' ),
                ( $controller ?? false ? "controller(".$this->designService->setFileNameStudlyCase($this->router)."Controller::class)\n->" : '' ),
            ],
            $stub);
    }

    public function setMiddleware($stub): array|string
    {
        if($this->withMiddleware)
            $middleware = $this->designService->setCustomNameSpace("Http\\Middleware\\".
                $this->designService->setFileNameStudlyCase($this->router));

        return str_replace(
            [
                "{{middlewareNameSpace}}",
                "{{middleware}}"
            ],
            [
                ( $middleware ?? false ? "use {$middleware};" : '' ),
                ( $middleware ?? false ? "middleware(['auth:sanctum', ".$this->designService->setFileNameStudlyCase($this->router)."::class])->" : '' )
            ],
            $stub);
    }
}
