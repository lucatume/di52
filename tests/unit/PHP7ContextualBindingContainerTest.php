<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class PHP7ContextualBindingContainerTest extends TestCase
{

    /**
     * @beforeClass
     */
    public static function before_all()
    {
        if (PHP_VERSION_ID < 70000) {
            return;
        }

        require_once __DIR__.'/data/test-contextual-classes-php7.php';
    }

    /**
     * @before
     */
    public function before_each()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped();
        }
    }

    /**
     * @test
     */
    public function it_should_resolve_primitive_contextual_bindings_in_a_php7_class()
    {
        $container = new Container();

        $container->when(Primitive7ConstructorClass::class)
            ->needs('$num')
            ->give(15);

        $container->when(Primitive7ConstructorClass::class)
            ->needs('$hello')
            ->give(function () {
                return 'World';
            });

        $container->when(Primitive7ConstructorClass::class)
            ->needs('$list')
            ->give([
                'one',
                'two',
            ]);

        $instance = $container->get(Primitive7ConstructorClass::class);

        $this->assertSame(15, $instance->num());
        $this->assertInstanceOf(Concrete7Dependency::class, $instance->dependency());
        $this->assertSame('World', $instance->hello());
        $this->assertSame(['one', 'two'], $instance->list());
        $this->assertNull($instance->optional());
    }

    /**
     * @test
     */
    public function it_should_resolve_primitive_contextual_bindings_in_a_php7_class_when_its_bound_interface_is_resolved()
    {
        $container = new Container();

        $container->bind( Test7Interface::class, Primitive7ConstructorClass::class );

        $container->when(Primitive7ConstructorClass::class)
            ->needs('$num')
            ->give(15);

        $container->when(Primitive7ConstructorClass::class)
            ->needs('$hello')
            ->give(function () {
                return 'World';
            });

        $container->when(Primitive7ConstructorClass::class)
            ->needs('$list')
            ->give([
                'one',
                'two',
            ]);

        $instance = $container->get(Test7Interface::class);

        $this->assertSame(15, $instance->num());
        $this->assertInstanceOf(Concrete7Dependency::class, $instance->dependency());
        $this->assertSame('World', $instance->hello());
        $this->assertSame(['one', 'two'], $instance->list());
        $this->assertNull($instance->optional());
    }

    /**
     * @test
     */
    public function it_should_throw_container_exception_when_missing_bindings_in_a_php7_class()
    {
        $this->expectException(ContainerException::class);

        $container = new Container();

        $container->get(Primitive7ConstructorClass::class);
    }
}
