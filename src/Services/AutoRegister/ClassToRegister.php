<?php
namespace Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister;

use ReflectionClass;
use Illuminate\Support\Collection;
use Henrotaym\LaravelHelpers\Facades\Helpers;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegistrableContract;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\ClassToRegisterContract;

/** Representing a class that should be registered. */
class ClassToRegister implements ClassToRegisterContract
{
    /**
     * Reflection of given class.
     * 
     * @var ReflectionClass|null
     */
    protected $reflected;

    /**
     * Query class.
     * 
     * @var string
     */
    protected $class;

    /**
     * @var string|null
     */
    protected $registrable_interface;

    /**
     * Interfaces implemented by class.
     * @var Collection
     */
    protected $interfaces;

    /**
     * Setting class name.
     *  
     * @param string $class
     * @return ClassToRegisterContract
     */
    public function setClass(string $class): ClassToRegisterContract
    {
        $this->class = $class;
        
        return $this->setReflected()
            ->setInterfaces();
    }

    protected function setReflected(): self
    {
        $this->reflected = class_exists($this->class)
            ? new ReflectionClass($this->class)
            : null;

        return $this;
    }

    protected function setInterfaces(): self
    {
        $this->interfaces = collect($this->reflected?->getInterfaceNames() ?? []);

        return $this->setRegistrableInterface();
    }

    protected function setRegistrableInterface(): self
    {
        if (!$this->interfaces->contains(AutoRegistrableContract::class)):
            $this->registrable_interface = null;

            return $this;
        endif;

        $expected_interface = $this->reflected->getShortName() . "Contract";

        $this->registrable_interface = $this->interfaces->first(function(string $interface) use ($expected_interface) {
            return $expected_interface === (new ReflectionClass($interface))->getShortName();
        });

        return $this;
    }

    /**
     * Registering query.
     * 
     * @return bool
     */
    public function register(): bool
    {
        if(!$this->isRegistrable()):
            return false;
        endif;

        app()->bind($this->registrable_interface, $this->class);
        
        return true;
    }

    /**
     * Telling if this query can be registered.
     * 
     * @return bool
     */
    public function isRegistrable(): bool
    {
        return !!$this->registrable_interface;
    }
}