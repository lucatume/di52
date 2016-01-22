<?php

class ArrayAccessBindingTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should allow binding a singleton interface to a concrete class implementation
     */
    public function it_should_allow_binding_a_singleton_interface_to_a_concrete_class_implementation()
    {
        $container = new tad_DI52_Container();

        $container['TestInterface'] = 'ConcreteClassImplementingTestInterfaceOne';

        $out = $container['TestInterface'];
        $out2 = $container['TestInterface'];

        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceOne', $out);
        $this->assertSame($out, $out2);
    }

    /**
     * @test
     * it should allow binding a singleton class to a concrete class implementation
     */
    public function it_should_allow_binding_a_singleton_class_to_a_concrete_class_implementation()
    {
        $container = new tad_DI52_Container();

        $container['ConcreteClassImplementingTestInterfaceOne'] = 'ExtendingClassOne';

        $out = $container['ConcreteClassImplementingTestInterfaceOne'];
        $out2 = $container['ConcreteClassImplementingTestInterfaceOne'];

        $this->assertInstanceOf('ExtendingClassOne', $out);
        $this->assertSame($out, $out2);
    }

    /**
     * @test
     * it should allow binding an a singleton callback to an interface
     */
    public function it_should_allow_binding_an_a_singleton_callback_to_an_interface()
    {
        $container = new tad_DI52_Container();

        $container['TestInterfaceOne'] = function () {
            return microtime();
        };

        $out = $container['TestInterfaceOne'];
        $out2 = $container['TestInterfaceOne'];

        $this->assertInternalType('string', $out);
        $this->assertSame($out, $out2);
    }

    /**
     * @test
     * it should allow binding a singleton callback to a concrete class implementation
     */
    public function it_should_allow_binding_a_singleton_callback_to_a_concrete_class_implementation()
    {
        $container = new tad_DI52_Container();

        $container['ConcreteClassImplementingTestInterfaceOne'] = function () {
            return microtime();
        };

        $out = $container['ConcreteClassImplementingTestInterfaceOne'];
        $out2 = $container['ConcreteClassImplementingTestInterfaceOne'];

        $this->assertInternalType('string', $out);
        $this->assertSame($out, $out2);
    }

    /**
     * @test
     * it should be able to bind an instance to an interface
     */
    public function it_should_be_able_to_bind_an_instance_to_an_interface()
    {
        $container = new tad_DI52_Container();

        $object = new stdClass();
        $container['TestInterfaceOne'] = $object;

        $out = $container['TestInterfaceOne'];
        $out2 = $container['TestInterfaceOne'];

        $this->assertSame($out, $object);
        $this->assertSame($out2, $object);
    }

    /**
     * @test
     * it should be able to bind an instance to a concrete class implementation
     */
    public function it_should_be_able_to_bind_an_instance_to_a_concrete_class_implementation()
    {
        $container = new tad_DI52_Container();

        $object = new stdClass();
        $container['ConcreteClassImplementingTestInterfaceOne'] = $object;

        $out = $container['ConcreteClassImplementingTestInterfaceOne'];
        $out2 = $container['ConcreteClassImplementingTestInterfaceOne'];

        $this->assertSame($out, $object);
        $this->assertSame($out2, $object);
    }
}
