<?php
namespace Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister;

use ReflectionClass;
use Illuminate\Support\Collection;
use Henrotaym\LaravelHelpers\Facades\Helpers;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Exceptions\FolderNotFound;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegisterContract;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\ClassToRegisterContract;

/** Registering classes. */
class AutoRegister implements AutoRegisterContract
{
    /**
     * Classes we're trying to register.
     * 
     * @var Collection
     */
    protected $classes;

    /**
     * Getting classes we're wokring on.
     * 
     * @return Collection
     */
    protected function getClasses(): Collection
    {
        return $this->classes ?? ($this->classes = collect());
    }

    /**
     * Adding given folder.
     * 
     * @param string $path
     * @param string $namespace
     * @return self
     */
    protected function addFolder(string $path, string $namespace): self
    {
        foreach (scandir($path) as $file):
            $this->addFile($file, $path, $namespace);
        endforeach;

        return $this;
    }

    /**
     * Adding given file.
     * 
     * @param string $file
     * @param string $actual_path
     * @param string $namespace
     * @return void
     */
    protected function addFile(string $file, string $actual_path, string $namespace): void
    {
        if ($file === "." || $file === ".."):
            return;
        endif;

        $file_path = "$actual_path/$file";
        $file_namespace = "$namespace\\" . ucfirst($file);

        // Recursively calling itself for subdirectories.
        if (is_dir($file_path)):
            $this->addFolder($file_path, $file_namespace);
            return;
        endif;

        // Removing file extension for classname.
        $class = substr($file_namespace, 0, strrpos($file_namespace, '.'));

        $class_to_register = app()->make(ClassToRegisterContract::class)
            ->setClass($class);

        $this->registerClass($class_to_register);
    }

    /**
     * Scanning and registering given folder.
     * 
     * @param string $path Path to scan.
     * @param string $namespace Default namespace for path.
     * 
     * @return Collection|null
     */
    public function scan(string $path, string $namespace): ?Collection
    {
        if (!file_exists($path)):
            report(FolderNotFound::path($path));
            return null;
        endif;

        $this->addFolder($path, $namespace);

        return $this->getClasses();
    }

    /**
     * Scanning and registering classes in folder where given class is defined.
     * 
     * @param string $class.
     * 
     * @return Collection|null
     */
    public function scanWhere(string $class): ?Collection
    {
        if (!class_exists($class)):
            return null;
        endif;

        $reflection = new ReflectionClass($class);
        $namespace = $reflection->getNamespaceName();
        $file_name = $reflection->getFileName();
        $path = substr($file_name, 0, (strrpos($file_name, "\\") ?: strrpos($file_name, "/")));

        return $this->scan($path, $namespace);
    }

    /**
     * Adding this class to those we should register.
     * 
     * @param string $class.
     * 
     * @return Collection|null
     */
    public function add(string $class): ?Collection
    {
        if (!class_exists($class)):
            return null;
        endif;

        $class_to_register = app()->make(ClassToRegisterContract::class)
            ->setClass($class);

        return $this->registerClass($class_to_register);
    }

    /**
     * Registering class if possible and adding it to registered one if sucessfull.
     * 
     * @param ClassToRegisterContract $class_to_register
     * @return Collection Classes successfully registered till now.
     */
    protected function registerClass(ClassToRegisterContract $class_to_register): Collection
    {
        if ($class_to_register->register()):
            $this->getClasses()->push($class_to_register);
        endif;

        return $this->getClasses();
    }
}