<?php

namespace unit;

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;
use unit\data\MySingletonClass;
use unit\data\MySingletonClassTwo;

require_once __DIR__ . '/data/MySingletonClass.php';
require_once __DIR__ . '/data/MySingletonClassTwo.php';

class UnsetTest extends TestCase
{
    public function test_unset_binding_with_bind_default():void
    {
        $container = new Container(false);

        $container->bind(MySingletonClass::class, MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertNotSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $previousInstance = $container->get(MySingletonClass::class);

        $container->offsetUnset(MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertNotSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $this->assertNotSame($previousInstance, $container->get(MySingletonClass::class));
    }

    public function test_unset_binding_with_singleton_default():void
    {
        $container = new Container(true);

        $container->bind(MySingletonClass::class, MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertNotSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $previousInstance = $container->get(MySingletonClass::class);

        $container->offsetUnset(MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $this->assertNotSame($previousInstance, $container->get(MySingletonClass::class));
    }

    public function test_unset_singleton_with_bind_default(): void
    {
        $container = new Container(false);

        $container->singleton(MySingletonClass::class, MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $previousInstance = $container->get(MySingletonClass::class);

        $container->offsetUnset(MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertNotSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $this->assertNotSame($previousInstance, $container->get(MySingletonClass::class));
    }

    public function test_unset_singleton_with_singleton_default(): void
    {
        $container = new Container(true);

        $container->singleton(MySingletonClass::class, MySingletonClass::class);
        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $previousInstance = $container->get(MySingletonClass::class);

        $container->offsetUnset(MySingletonClass::class);

        $this->assertInstanceOf(MySingletonClass::class, $container->get(MySingletonClass::class));
        $this->assertSame($container->get(MySingletonClass::class), $container->get(MySingletonClass::class));
        $this->assertNotSame($previousInstance, $container->get(MySingletonClass::class));
    }

    public function test_unset_given_when_then_with_bind_default(): void
    {
        $container = new Container(false);

        $container->when(MySingletonClassTwo::class)
                  ->needs('$number')
                  ->give(10);

        $this->assertInstanceOf(MySingletonClassTwo::class, $container->get(MySingletonClassTwo::class));
        $this->assertNotSame($container->get(MySingletonClassTwo::class), $container->get(MySingletonClassTwo::class));

        $container->offsetUnset(MySingletonClassTwo::class);

        $this->expectException(ContainerException::class);

        $container->get(MySingletonClassTwo::class);
    }

    public function test_unset_given_when_then_with_singleton_default(): void
    {
        $container = new Container(true);

        $container->when(MySingletonClassTwo::class)
                  ->needs('$number')
                  ->give(10);

        $this->assertInstanceOf(MySingletonClassTwo::class, $container->get(MySingletonClassTwo::class));
        $this->assertSame($container->get(MySingletonClassTwo::class), $container->get(MySingletonClassTwo::class));

        $container->offsetUnset(MySingletonClassTwo::class);

        $this->expectException(ContainerException::class);

        $container->get(MySingletonClassTwo::class);
    }
}
