<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class ContextualBindingContainerTest extends TestCase
{

    /**
     * @beforeClass
     */
    public static function before_all()
    {
        require_once __DIR__ . '/data/test-contextual-classes-php.php';
    }

    /**
     * @test
     */
    public function it_should_resolve_primitive_contextual_bindings_in_a_PHP53_class()
    {
        $container = new Container();

        $container->when(Primitive53ConstructorClass::class)
            ->needs('$num')
            ->give(15);

        $container->when(Primitive53ConstructorClass::class)
            ->needs('$hello')
            ->give(function () {
                return 'World';
            });

        $container->when(Primitive53ConstructorClass::class)
            ->needs('$list')
            ->give([
                'one',
                'two',
            ]);

        $instance = $container->get(Primitive53ConstructorClass::class);

        $this->assertSame(15, $instance->num());
        $this->assertInstanceOf(Concrete53Dependency::class, $instance->dependency());
        $this->assertSame('World', $instance->hello());
        $this->assertSame([ 'one', 'two' ], $instance->getList());
        $this->assertNull($instance->optional());
    }

    /**
     * @test
     */
    public function it_should_throw_container_exception_when_missing_bindings_in_a_PHP53_class()
    {
        $this->expectException(ContainerException::class);

        $container = new Container();

        $container->get(Primitive53ConstructorClass::class);
    }
}
