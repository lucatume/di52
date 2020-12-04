<?php

use lucatume\DI52\Container;
use lucatume\DI52\NotFoundException;
use PHPUnit\Framework\TestCase;

class PSR111Test extends TestCase
{
    /**
     * It should return has values correctly for string ids
     *
     * @test
     */
    public function should_return_has_values_correctly_for_string_ids()
    {
        $container = new Container();

        $container->bind('bound', new stdClass());
        $container->singleton('singleton', new stdClass());

        $this->assertTrue($container->has('bound'));
        $this->assertTrue($container->has('singleton'));
        $this->assertFalse($container->has('not-bound'));
    }

    /**
     * It should return has values correctly for class names
     *
     * @test
     */
    public function should_return_has_values_correctly_for_class_names()
    {
        $container = new Container();

        $container->bind(Car::class, Car::class);
        $container->singleton(Engine::class, Engine::class);

        $this->assertTrue($container->has(Car::class));
        $this->assertTrue($container->has(Engine::class));
    }

    /**
     * It should state the container has an unbound existing class
     *
     * @test
     */
    public function should_state_the_container_has_an_unbound_existing_class()
    {
        $container = new Container();
        $this->assertTrue($container->has(Engine::class));
    }

    public function should_state_the_container_has_a_non_buildable_class()
    {
        $container = new Container();
        $this->assertTrue($container->has(PrivateConstructor::class));
    }

    /**
     * It should not have a non buildable class
     *
     * @test
     */
    public function should_not_have_a_non_buildable_class()
    {
        $container = new Container();
        $this->assertFalse($container->has('SomeClassThatDoesNotExist'));
    }

    /**
     * It should have and not have variables
     *
     * @test
     */
    public function should_have_and_not_have_variables()
    {
        $container = new Container();
        $container->setVar('one', 23);
        $container['two'] = 89;

        $this->assertTrue($container->has('one'));
        $this->assertTrue($container->has('two'));
        $this->assertFalse($container->has('three'));
    }

    /**
     * It should have classes bound with ArrayAccess API
     *
     * @test
     */
    public function should_have_classes_bound_with_array_access_api()
    {
        $container = new Container();
        $container['car'] = Car::class;
        $container['engine'] = Engine::class;
        $container[Valve::class] = Valve::class;
        $container[Clutch::class] = Clutch::class;

        $this->assertTrue($container->has('car'));
        $this->assertTrue($container->has('engine'));
        $this->assertTrue($container->has(Valve::class));
        $this->assertTrue($container->has(Clutch::class));
    }

    /**
     * It should allow getting a bound id
     *
     * @test
     */
    public function should_allow_getting_a_bound_id()
    {
        $container        = new Container();
        $classOneInstance = new ClassOne();
        $container->bind(One::class, ClassOne::class);
        $container['one'] = $classOneInstance;
        $container->bind(ClassSixOne::class, ClassSixOne::class);
        $container->singleton(ClassSix::class);
        $container->setVar('var1', 23);
        $container['var2'] = 89;

        $this->assertSame($classOneInstance, $container->get('one'));
        $this->assertInstanceOf(ClassSixOne::class, $container->get(ClassSixOne::class));
        $this->assertInstanceOf(ClassSix::class, $container->get(ClassSix::class));
        $this->assertEquals(23, $container->get('var1'));
        $this->assertEquals(89, $container->get('var2'));
    }

    /**
     * It should allow getting an unbound buildable
     *
     * @test
     */
    public function should_allow_getting_an_unbound_buildable()
    {
        $container        = new Container();

        $this->assertInstanceOf(ClassOne::class, $container->get(ClassOne::class));
    }

    /**
     * It should throw NotFoundException if trying to get unbound not buildable
     *
     * @test
     */
    public function should_throw_not_found_exception_if_trying_to_get_unbound_not_buildable()
    {
        $container        = new Container();

        $this->expectException(NotFoundException::class);

        $container->get(One::class);
    }
}
