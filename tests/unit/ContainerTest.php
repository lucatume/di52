<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use lucatume\DI52\NotFoundException;
use PHPUnit\Framework\TestCase;

class MissingBootMethodServiceProvider
{
    public function isDeferred()
    {
        return false;
    }

    public function register()
    {
    }
}

class PrivateConstructorClass
{
    private function __construct()
    {
    }
}

class ContainerTest extends TestCase
{
    /**
     * @beforeClass
     */
    public static function before_all()
    {
        \spl_autoload_register(static function ($class) {
            if (strpos($class, 'FatalErrorClass') === 0) {
                require_once __DIR__ . "/data/{$class}.php";
            }
        });
    }

    /**
     * It should throw if trying to register provider class with private boot method
     *
     * @test
     */
    public function should_throw_if_trying_to_register_provider_class_with_private_boot_method()
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        $container->register(MissingBootMethodServiceProvider::class);
    }

    /**
     * It should correctly handle id-only binding of non instantiatable class
     *
     * @test
     */
    public function should_correctly_handle_id_only_binding_of_non_instantiatable_class()
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        $container->bind(PrivateConstructor::class);
        $container->bind(PrivateConstructor::class);
        $container->bind(PrivateConstructor::class);
    }

    /**
     * It should correctly handle id-only binding of private constructor class
     *
     * @test
     */
    public function should_correctly_handle_id_only_binding_of_private_constructor_class()
    {
        $container = new Container();

        for ($i=0; $i<2; $i++) {
            try {
                $container->bind(PrivateConstructorClass::class);
            } catch (ContainerException $e) {
                $this->assertInstanceOf(ContainerException::class, $e);
            }
        }
    }

    /** @test */
    public function it_should_resolve_contextual_binding_without_an_early_bind()
    {
        $container = new Container();

        $container->when('ClassSix')
            ->needs('One')
            ->give('ClassOne');

        $this->assertInstanceOf('ClassOne', $container->make('ClassSix')->getOne());
    }

    /** @test */
    public function it_should_resolve_contextual_binding_with_an_early_bind_of_different_type()
    {
        $container = new Container();

        $container->bind('One', 'foo');

        $container->when('ClassSix')
            ->needs('One')
            ->give('ClassOne');

        $this->assertInstanceOf('ClassOne', $container->make('ClassSix')->getOne());
    }

    /**
     * It should throw when getting provider from non-provider binding
     *
     * @test
     */
    public function should_throw_when_getting_provider_from_non_provider_binding()
    {
        $container = new Container();

        $container->singleton('testProvider', new ClassOne());

        try {
            $container->getProvider('testProvider');
        } catch (Exception $e) {
            $this->assertInstanceOf(NotFoundException::class, $e);
        }
    }

    /**
     * The container now registers itself inside itself (containerception).
     *
     * @test
     */
    public function it_should_contain_the_containers_own_instance()
    {
        $container = new Container();

        $this->assertTrue($container->has(Container::class));
        $this->assertSame($container, $container->get(Container::class));

        $container->get(ClassOne::class);
        $container->get(ClassOneOne::class);

        $this->assertTrue($container->has(ClassOne::class));
        $this->assertTrue($container->has(ClassOneOne::class));
        $this->assertSame($container, $container->get(Container::class));
    }
}
