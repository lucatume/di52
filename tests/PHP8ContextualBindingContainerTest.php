<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class PHP8ContextualBindingContainerTest extends TestCase {

    /**
     * @beforeClass
     */
    public static function before_all()
    {
        if (PHP_VERSION_ID < 80000) {
            return;
        }

        require_once __DIR__ . '/data/test-contextual-classes-php8.php';
    }

    /**
     * @before
     */
    public function before_each()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped();
        }
    }

    /**
     * @test
     */
    public function it_should_resolve_primitive_contextual_bindings_in_a_PHP8_class()
    {
        $container = new Container();

        $container->when( Primitive8ConstructorClass::class )
            ->needs( '$num' )
            ->give( 20 );

        $container->when( Primitive8ConstructorClass::class )
            ->needs( '$hello' )
            ->give( function () { return 'World'; } );

        $container->when( Primitive8ConstructorClass::class )
            ->needs( '$list' )
            ->give( [
                'one',
                'two',
            ] );

        $instance = $container->get( Primitive8ConstructorClass::class );

        $this->assertSame( 20, $instance->num() );
        $this->assertInstanceOf( Concrete8Dependency::class, $instance->dependency() );
        $this->assertSame( 'World', $instance->hello() );
        $this->assertSame( [ 'one', 'two' ], $instance->list() );
        $this->assertNull( $instance->optional() );
    }

    /**
     * @test
     */
    public function it_should_throw_container_exception_when_missing_bindings_in_a_PHP8_class()
    {
        $this->expectException(ContainerException::class);

        $container = new Container();

        $container->get(Primitive8ConstructorClass::class);
    }

}
