<?php
use tad_DI52_Container as DI;

class InterfaceBindingTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * it should allow binding an interface to a concret class name
     */
    public function it_should_allow_binding_an_interface_to_a_concret_class_name()
    {
        $bindingsResolver = $this->prophesize('tad_DI52_Bindings_ResolverInterface');
        $bindingsResolver->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne', false)->shouldBeCalled();
        $bindingsResolver->resolve('TestInterfaceOne')->shouldBeCalled();

        $container = new DI();
        $container->_setBindingsResolver($bindingsResolver->reveal());
        $container->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $container->make('TestInterfaceOne');
    }
}
