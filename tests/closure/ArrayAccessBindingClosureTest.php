<?php

class ArrayAccessBindingClosureTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * it should allow binding an a singleton callback to an interface
     */
    public function it_should_allow_binding_an_a_singleton_callback_to_an_interface()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

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
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

        $container = new tad_DI52_Container();

        $container['ConcreteClassImplementingTestInterfaceOne'] = function () {
            return microtime();
        };

        $out = $container['ConcreteClassImplementingTestInterfaceOne'];
        $out2 = $container['ConcreteClassImplementingTestInterfaceOne'];

        $this->assertInternalType('string', $out);
        $this->assertSame($out, $out2);
    }
}
