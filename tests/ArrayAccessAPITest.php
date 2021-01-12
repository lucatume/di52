<?php

use lucatume\DI52\Container;
use lucatume\DI52\NotFoundException;
use lucatume\DI52\ServiceProvider;
use PHPUnit\Framework\TestCase;

class ArrayAccessTestClassOne
{
}

class ArrayAccessTestClassTwo implements ArrayAccessInterfaceOne
{
    use ArrayAccessTraitOne;
}

class ArrayAccessTestClassThree implements ArrayAccessInterfaceOne
{
    use ArrayAccessTraitOne;
}

class ArrayAccessTestClassPrivateConstructor
{
    private function __construct()
    {
    }
}

abstract class ArrayAccessAbstractClass
{
}

trait ArrayAccessTraitOne
{
}

interface ArrayAccessInterfaceOne
{
}

class ArrayAccessServiceProvider extends ServiceProvider
{
    public function register()
    {
    }
}

class ArrayAccessAPITest extends TestCase
{
    /**
     * It should correctly detect if offset exists
     *
     * @test
     */
    public function should_correctly_detect_if_offset_exists()
    {
        $container = new Container();

        $container->bind('array-access-one', new stdClass());

        $this->assertTrue(isset($container['array-access-one']));
        $this->assertFalse(isset($container['array-access-two']));
    }

    /**
     * It should mark unbound concrete class as existing
     *
     * @test
     */
    public function should_mark_unbound_concrete_class_as_existing()
    {
        $container = new Container();

        $this->assertTrue(isset($container[ArrayAccessTestClassOne::class]));
    }

    /**
     * It should mark unbound trait as not existing
     *
     * @test
     */
    public function should_mark_unbound_trait_as_not_existing()
    {
        $container = new Container();

        $this->assertFalse(isset($container[ArrayAccessTraitOne::class]));
    }

    /**
     * It should mark unbound interface as not existing
     *
     * @test
     */
    public function should_mark_unbound_interface_as_not_existing()
    {
        $container = new Container();

        $this->assertFalse(isset($container[ArrayAccessInterfaceOne::class]));
    }

    /**
     * It should mark private constructor class as existing
     *
     * @test
     */
    public function should_mark_private_constructor_class_as_existing()
    {
        $container = new Container();

        $this->assertTrue(isset($container[ArrayAccessTestClassPrivateConstructor::class]));
    }

    /**
     * It should mark unbound abstract class as existing
     *
     * @test
     */
    public function should_mark_unbound_abstract_class_as_existing()
    {
        $container = new Container();

        $this->assertTrue(isset($container[ArrayAccessAbstractClass::class]));
    }

    /**
     * It should correctly mark existence and build bound interface
     *
     * @test
     */
    public function should_correctly_mark_existence_and_build_bound_interface()
    {
        $container = new Container();

        $interface               = ArrayAccessInterfaceOne::class;
        $two                     = ArrayAccessTestClassTwo::class;
        $container[ $interface ] = $two;

        $this->assertTrue(isset($container[ $interface ]));
        $this->assertInstanceOf($two, $container[ $interface ]);
        $this->assertInstanceOf($interface, $container[ $interface ]);
        $this->assertSame($container[ $interface ], $container[ $interface ]);
    }

    /**
     * It should correctly mark existence and build bound trait
     *
     * @test
     */
    public function should_correctly_mark_existence_and_build_bound_trait()
    {
        $container = new Container();

        $trait               = ArrayAccessTraitOne::class;
        $two               = ArrayAccessTestClassTwo::class;
        $container[ $trait ] = $two;

        $this->assertTrue(isset($container[ $trait ]));
        $this->assertInstanceOf($two, $container[ $trait ]);
        $this->assertSame($container[ $trait ], $container[ $trait ]);
    }

    /**
     * It should allow unsetting a binding
     *
     * @test
     */
    public function should_allow_unsetting_a_binding()
    {
        $container = new Container();

        $interface               = ArrayAccessInterfaceOne::class;
        $two                     = ArrayAccessTestClassTwo::class;
        $container[ $interface ] = $two;

        $this->assertInstanceOf($two, $container[ $interface ]);

        unset($container[ $interface ]);

        $this->expectException(NotFoundException::class);

        $container[ $interface ];

        $three                   = ArrayAccessTestClassThree::class;
        $container[ $interface ] = $three;

        $this->assertInstanceOf($three, $container[ $interface ]);
    }

    /**
     * It should allow getting a registered service provider
     *
     * @test
     */
    public function should_allow_getting_a_registered_service_provider()
    {
        $container = new Container();

        $providerClass = ArrayAccessServiceProvider::class;
        $container->register($providerClass);

        $this->assertInstanceOf($providerClass, $container[ $providerClass ]);
        $this->assertSame($container[ $providerClass ], $container[$providerClass]);
    }
}
