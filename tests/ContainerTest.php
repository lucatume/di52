<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
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
    public static function setUpBeforeClass()
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

        $container->bind(FatalErrorClassSeven::class);
        $container->bind(FatalErrorClassSeven::class);
        $container->bind(FatalErrorClassSeven::class);
    }

    /**
     * It should correclty handle id-only binding of private constructor class
     *
     * @test
     */
    public function should_correclty_handle_id_only_binding_of_private_constructor_class()
    {
        $container = new Container();

        for ($i=0; $i<2; $i++) {
            try {
                $container->bind(PrivateConstructorClass::class);
            } catch (ContainerException $e) {
                // no-op
            }
        }
    }
}
