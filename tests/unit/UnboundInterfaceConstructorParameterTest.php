<?php

namespace unit;

use Acme\DependingOnOneInterfaceWithDefault;
use Acme\DependingOnOneInterfaceWithoutDefault;
use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class UnboundInterfaceConstructorParameterTest extends TestCase
{
    /**
     * It should throw if constructor parameter is interface with no default value
     *
     * @test
     */
    public function should_throw_if_constructor_parameter_is_interface_with_no_default_value()
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        $container->get(DependingOnOneInterfaceWithoutDefault::class);
    }

    /**
     * It should not throw if constructor parameter is interface not bound but comes with default
     *
     * @test
     */
    public function should_not_throw_if_constructor_parameter_is_interface_not_bound_but_comes_with_default()
    {
        $container = new Container();

        $built = $container->get(DependingOnOneInterfaceWithDefault::class);

        $this->assertInstanceOf(DependingOnOneInterfaceWithDefault::class, $built);
    }
}
