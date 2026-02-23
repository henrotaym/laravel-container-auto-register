<?php
namespace Henrotaym\LaravelContainerAutoRegister\Tests\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Henrotaym\LaravelContainerAutoRegister\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\AutoRegister;
use Henrotaym\LaravelContainerAutoRegister\Testing\Contracts\AppQueryContract;
use Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister\QueryAutoRegistrable;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Exceptions\FolderNotFound;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegisterContract;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\ClassToRegisterContract;
use Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister\Contracts\QueryAutoRegistrableContract;
use Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister\Contracts\QueryNotAutoRegistrableContract;
use Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister\Nested\NestedAgain\QueryAutoRegistrable as NestedQueryAutoRegistrable;
use Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister\Contracts\Nested\NestedAgain\QueryAutoRegistrableContract as NestedQueryAutoRegistrableContract;
use Henrotaym\LaravelPackageVersioning\Testing\Traits\InstallPackageTest;

class AutoRegisterTest extends TestCase
{
    use InstallPackageTest;

    public function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function auto_register_not_registering_not_auto_registrable()
    {
        $this->scanFolder();
        $this->expectException(BindingResolutionException::class);   
        app()->make(QueryNotAutoRegistrableContract::class);
    }

    #[Test]
    public function auto_register_registering_auto_registrable()
    {
        $this->scanFolder();
        $this->assertInstanceOf(QueryAutoRegistrable::class, app()->make(QueryAutoRegistrableContract::class));
    }

    #[Test]
    public function auto_register_registering_nested_auto_registrable()
    {
        $this->scanFolder();
        $this->assertInstanceOf(NestedQueryAutoRegistrable::class, app()->make(NestedQueryAutoRegistrableContract::class));
    }

    #[Test]
    public function auto_register_reporting_wrong_path()
    {
        $path = __DIR__ . "/testastos";

        Log::shouldReceive('error')
            ->withArgs(function($message) use ($path) {
                return $message === FolderNotFound::path($path)->getMessage();
            });

        $result = $this->getAutoRegister()->scan($path, "Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister");
        $this->assertNull($result);
    }

    #[Test]
    public function auto_register_scanning_where_returning_null_if_invalid_class()
    {
        $this->mockAutoRegister();

        $this->mocked_auto_register->expects()->scanWhere($this->undefined_class)->passthru();
        $this->mocked_auto_register->expects()->scan()->withAnyArgs()->times(0);

        $this->assertNull($this->mocked_auto_register->scanWhere($this->undefined_class));
    }

    #[Test]
    public function auto_register_scanning_correctly_with_correct_class()
    {
        $this->mockAutoRegister();

        $this->mocked_auto_register->expects()->scanWhere($this->valid_class)->passthru();
        $this->mocked_auto_register->expects()->scan()
            ->with( realpath(__DIR__ .'/AutoRegister'), 'Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister')
            ->andReturn(collect());

        $this->assertNotNull($this->mocked_auto_register->scanWhere($this->valid_class));
    }

    #[Test]
    public function auto_register_add_single_class_return_null_if_not_a_class()
    {
        $this->assertNull($this->getAutoRegister()->add($this->undefined_class));
    }

    #[Test]
    public function auto_register_add_single_class_return_registered_classes_if_valid()
    {
        $this->assertCount(
            1,
            $this->getAutoRegister()
                ->add($this->valid_class)
        );
        $this->assertInstanceOf($this->valid_class, app()->make(QueryAutoRegistrableContract::class));
    }

    /** @var MockInterface */
    protected $mocked_auto_register;

    protected function mockAutoRegister()
    {
        $this->mocked_auto_register = $this->mockThis(AutoRegister::class);

        return $this;
    }

    /** @var MockInterface */
    protected $mocked_class_to_register;

    protected function mockClassToRegister()
    {
        $this->mocked_class_to_register = $this->mockThis(ClassToRegisterContract::class);

        return $this;
    }

    /** @var string */
    protected $undefined_class = "testastos";

    /** @var string */
    protected $valid_class = QueryAutoRegistrable::class;


    protected function getAutoRegister(): AutoRegister
    {
        return app(AutoRegisterContract::class);
    }

    protected function scanFolder(?string $path = null)
    {
        $path = $path ?? __DIR__ . '/AutoRegister';
        $namespace = "Henrotaym\LaravelContainerAutoRegister\Tests\Unit\AutoRegister";
        
        $this->getAutoRegister()->scan($path, $namespace);
    }
}