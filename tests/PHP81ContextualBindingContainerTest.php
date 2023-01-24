<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class PHP81ContextualBindingContainerTest extends TestCase {

    /**
     * @beforeClass
     */
    public static function before_all()
    {
        if (PHP_VERSION_ID < 80100) {
            return;
        }

        require_once __DIR__ . '/data/test-contextual-classes-php81.php';
    }

    /**
     * @before
     */
    public function before_each()
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped();
        }
    }

    public function test_it_should_resolve_primitive_contextual_bindings_in_PHP81_class()
    {
        $container = new Container();

        $container->when( Primitive81ConstructorClass::class )
            ->needs( '$num' )
            ->give( 30 );

        $container->when( Primitive81ConstructorClass::class )
            ->needs( '$hello' )
            ->give( function () { return 'World'; } );

        $container->when( Primitive81ConstructorClass::class )
            ->needs( '$list' )
            ->give( [
                'one',
                'two',
            ] );

        $instance = $container->get( Primitive81ConstructorClass::class );

        $this->assertSame( 30, $instance->num() );
        $this->assertInstanceOf( Concrete81Dependency::class, $instance->dependency() );
        $this->assertSame( 'World', $instance->hello() );
        $this->assertSame( [ 'one', 'two' ], $instance->list() );
        $this->assertNull( $instance->optional() );
    }

    public function test_it_should_throw_container_exception_when_missing_bindings_in_PHP81_class()
    {
        $this->expectException(ContainerException::class);

        $container = new Container();

        $container->get(Primitive81ConstructorClass::class);
    }

    public function test_it_resolves_an_enum_as_dependency()
    {
        $this->assertTrue(enum_exists( Status::class));

        $container = new Container();

        $container->when(EnumAsADependencyClass::class)
            ->needs('$status')
            ->give(Status::PUBLISHED);

        $instance = $container->get(EnumAsADependencyClass::class);
        $enum = $instance->status();

        $this->assertInstanceOf(UnitEnum::class, $enum);
        $this->assertSame( Status::PUBLISHED, $enum );
    }

    public function test_it_resolves_an_enum_as_dependency_with_a_default_value()
    {
        $this->assertTrue(enum_exists( Status::class));

        $container = new Container();

        $instance = $container->get(EnumAsADependencyWithDefaultValueClass::class);
        $enum = $instance->status();

        $this->assertInstanceOf(UnitEnum::class, $enum);
        $this->assertSame( Status::DEFAULT, $enum );
    }

    public function test_it_resolves_a_backed_enum_as_dependency()
    {
        $this->assertTrue(enum_exists( StatusBacked::class));

        $container = new Container();

        $container->when(BackedEnumClass::class)
            ->needs('$status')
            ->give(StatusBacked::PUBLISHED);

        $instance = $container->get(BackedEnumClass::class);
        $enum = $instance->status();

        $this->assertInstanceOf(UnitEnum::class, $enum);
        $this->assertInstanceOf(BackedEnum::class, $enum);
        $this->assertSame( StatusBacked::PUBLISHED, $enum );
        $this->assertSame( 'published', $enum->value );
        $this->assertSame( 'PUBLISHED', $enum->name );
    }

    public function test_it_resolves_a_backed_enum_with_default_value_as_dependency()
    {
        $this->assertTrue(enum_exists( StatusBacked::class));

        $container = new Container();

        $instance = $container->get(BackedEnumWithDefaultValueClass::class);
        $enum = $instance->status();

        $this->assertInstanceOf(UnitEnum::class, $enum);
        $this->assertInstanceOf(BackedEnum::class, $enum);
        $this->assertSame( StatusBacked::DRAFT, $enum );
        $this->assertSame( 'draft', $enum->value );
        $this->assertSame( 'DRAFT', $enum->name );
    }

    public function test_it_resolves_a_backed_enum_as_a_union_type_as_dependency()
    {
        $this->assertTrue(enum_exists( StatusBacked::class));

        $container = new Container();

        $container->when(BackedEnumUnionClass::class)
            ->needs('$status')
            ->give(StatusBacked::PUBLISHED);

        $instance = $container->get(BackedEnumUnionClass::class);
        $status = $instance->status();

        $this->assertSame( 'published', $status );
    }

    public function test_it_resolves_a_string_as_a_union_type_as_dependency()
    {
        $container = new Container();

        $container->when(BackedEnumUnionClass::class)
            ->needs('$status')
            ->give('archived');

        $instance = $container->get(BackedEnumUnionClass::class);
        $status = $instance->status();

        $this->assertSame( 'archived', $status );
    }

    public function test_it_resolves_a_backed_enum_with_a_default_value_as_a_union_type_as_dependency()
    {
        $container = new Container();

        $instance = $container->get(BackedEnumUnionWithDefaultValueClass::class);
        $status = $instance->status();

        $this->assertSame( 'draft', $status );
    }

    public function test_it_resolves_a_class_with_two_enums_as_a_dependency()
    {
        $container = new Container();

        $container->when(DoubleEnumClass::class)
            ->needs('$status')
            ->give(Status::ARCHIVED);

        $container->when(DoubleEnumClass::class)
            ->needs('$statusBacked')
            ->give(StatusBacked::PUBLISHED);

        $instance = $container->get(DoubleEnumClass::class);

        $status = $instance->status();
        $this->assertSame(Status::ARCHIVED, $status);

        $statusBacked = $instance->statusBacked();
        $this->assertSame(StatusBacked::PUBLISHED, $statusBacked);
        $this->assertSame('published', $statusBacked->value);
        $this->assertSame('PUBLISHED', $statusBacked->name);
    }

}
