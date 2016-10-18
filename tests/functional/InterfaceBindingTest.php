<?php

class InterfaceBindingTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * it should allow binding an interface to a concrete class name
     */
    public function it_should_allow_binding_an_interface_to_a_concrete_class_name()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->expects($this->once())
            ->method('bind')
            ->with('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne', null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('TestInterfaceOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);
        $container->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a concrete class name to a concrete class name
     */
    public function it_should_allow_binding_a_concrete_class_name_to_a_concrete_class_name()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->expects($this->once())
            ->method('bind')
            ->with('ConcreteClassImplementingTestInterfaceOne', 'DependingClassTwo', null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('ConcreteClassImplementingTestInterfaceOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);

        $container->bind('ConcreteClassImplementingTestInterfaceOne', 'DependingClassTwo');
        $container->make('ConcreteClassImplementingTestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding an object instance to an interface
     */
    public function it_should_allow_binding_an_object_instance_to_an_interface()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $instance = new ConcreteClassImplementingTestInterfaceOne;
        $bindingsResolver->expects($this->once())
            ->method('bind')
            ->with('TestInterfaceOne', $instance, null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('TestInterfaceOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);

        $container->bind('TestInterfaceOne', $instance);
        $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a class not implementing an interface to an interface
     */
    public function it_should_allow_binding_a_class_not_implementing_an_interface_to_an_interface()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->expects($this->once())
            ->method('bind')
            ->with('TestInterfaceOne', 'ConcreteClassOne', null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('TestInterfaceOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);

        $container->bind('TestInterfaceOne', 'ConcreteClassOne');
        $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a concrete class non extending the bound class
     */
    public function it_should_allow_binding_a_concrete_class_non_extending_the_bound_class()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->expects($this->once())
            ->method('bind')
            ->with('ConcreteClassOne', 'ObjectOne', null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('ConcreteClassOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);

        $container->bind('ConcreteClassOne', 'ObjectOne');
        $container->make('ConcreteClassOne');
    }

    /**
     * @test
     * it should allow binding a singleton to an interface
     */
    public function it_should_allow_binding_a_singleton_to_an_interface()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->expects($this->once())
            ->method('singleton')
            ->with('TestInterfaceOne', 'ObjectOne', null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('TestInterfaceOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);

        $container->singleton('TestInterfaceOne', 'ObjectOne');
        $container->make('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding a singleton to a concrete class
     */
    public function it_should_allow_binding_a_singleton_to_a_concrete_class()
    {
        $bindingsResolver = $this->getMock('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->expects($this->once())
            ->method('singleton')
            ->with('ConcreteClassOne', 'ObjectOne', null);
        $bindingsResolver->expects($this->once())
            ->method('resolve')
            ->with('ConcreteClassOne');

        $container = new tad_DI52_Container();
        $container->setBindingsResolver($bindingsResolver);

        $container->singleton('ConcreteClassOne', 'ObjectOne');
        $container->make('ConcreteClassOne');
    }
}
