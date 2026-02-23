<?php
namespace Henrotaym\LaravelContainerAutoRegister\Providers;

use Henrotaym\LaravelContainerAutoRegister\Package;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\AutoRegister;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\ClassToRegister;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegisterContract;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\ClassToRegisterContract;
use Henrotaym\LaravelPackageVersioning\Providers\Abstracts\VersionablePackageServiceProvider;

class LaravelContainerAutoRegisterServiceProvider extends VersionablePackageServiceProvider
{
    protected function addToRegister(): void
    {
        $this->bindAutoRegisterService();
    }

    protected function addToBoot(): void
    {
        //
    }

    public static function getPackageClass(): string
    {
        return Package::class;
    }

    protected function bindAutoRegisterService(): self
    {
        $this->app->bind(ClassToRegisterContract::class, ClassToRegister::class);
        $this->app->bind(AutoRegisterContract::class, AutoRegister::class);

        return $this;
    }
}