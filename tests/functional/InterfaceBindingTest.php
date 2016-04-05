<?php
use Prophecy\Argument;
use tad_DI52_Container as DI;

class InterfaceBindingTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * it should allow binding an interface to a concrete class name
     */
    public function it_should_allow_binding_an_interface_to_a_concrete_class_name()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne', false)->shouldBeCalled();
        $bindingsResolver->resolve('TestInterfaceOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());
        $container->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a concrete class name to a concrete class name
     */
    public function it_should_allow_binding_a_concrete_class_name_to_a_concrete_class_name()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->bind('ConcreteClassImplementingTestInterfaceOne', 'DependingClassTwo', false)->shouldBeCalled();
        $bindingsResolver->resolve('ConcreteClassImplementingTestInterfaceOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());

        $container->bind('ConcreteClassImplementingTestInterfaceOne', 'DependingClassTwo');
        $container->make('ConcreteClassImplementingTestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding an object instance to an interface
     */
    public function it_should_allow_binding_an_object_instance_to_an_interface()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $instance = new ConcreteClassImplementingTestInterfaceOne;
        $bindingsResolver->bind('TestInterfaceOne', Argument::type('ConcreteClassImplementingTestInterfaceOne'), false)->shouldBeCalled();
        $bindingsResolver->resolve('TestInterfaceOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());

        $container->bind('TestInterfaceOne', $instance);
        $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a class not implementing an interface to an interface
     */
    public function it_should_allow_binding_a_class_not_implementing_an_interface_to_an_interface()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->bind('TestInterfaceOne', 'ConcreteClassOne', true)->shouldBeCalled();
        $bindingsResolver->resolve('TestInterfaceOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());

        $container->bind('TestInterfaceOne', 'ConcreteClassOne', true);
        $out = $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a concrete class non extending the bound class
     */
    public function it_should_allow_binding_a_concrete_class_non_extending_the_bound_class()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->bind('ConcreteClassOne', 'ObjectOne', true)->shouldBeCalled();
        $bindingsResolver->resolve('ConcreteClassOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());

        $container->bind('ConcreteClassOne', 'ObjectOne', true);
        $out = $container->make('ConcreteClassOne');
    }

    /**
     * @test
     * it should allow binding a singleton to an interface
     */
    public function it_should_allow_binding_a_singleton_to_an_interface()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->singleton('TestInterfaceOne', 'ObjectOne', true)->shouldBeCalled();
        $bindingsResolver->resolve('TestInterfaceOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());

        $container->singleton('TestInterfaceOne', 'ObjectOne', true);
        $out = $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a singleton to a concrete class
     */
    public function it_should_allow_binding_a_singleton_to_a_concrete_class()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->singleton('ConcreteClassOne', 'ObjectOne', true)->shouldBeCalled();
        $bindingsResolver->resolve('ConcreteClassOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());

        $container->singleton('ConcreteClassOne', 'ObjectOne', true);
        $out = $container->make('ConcreteClassOne');
    }
}
