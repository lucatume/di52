<?php

use lucatume\DI52\Container;
use PHPUnit\Framework\TestCase;

class CallbackClassOne
{
    public function getInstanceValue()
    {
        return 'instance-value';
    }
    public static function getStaticValue()
    {
        return 'static-value';
    }
}

class CallbackTest extends TestCase
{
    /**
     * It should allow creating callbacks for instance methods
     *
     * @test
     */
    public function should_allow_creating_callbacks_for_instance_methods()
    {
        $container = new Container();

        $closureOne = $container->callback(CallbackClassOne::class, 'getInstanceValue');
        $closureTwo = $container->callback(CallbackClassOne::class, 'getInstanceValue');

        $this->assertEquals('instance-value', $closureOne());
        $this->assertEquals('instance-value', $closureTwo());
        $this->assertNotSame($closureOne, $closureTwo);
    }

    /**
     * It should allow creating callbacks for static methods
     *
     * @test
     */
    public function should_allow_creating_callbacks_for_static_methods()
    {
        $container = new Container();

        $closureOne = $container->callback(CallbackClassOne::class, 'getStaticValue');
        $closureTwo = $container->callback(CallbackClassOne::class, 'getStaticValue');

        $this->assertEquals('static-value', $closureOne());
        $this->assertEquals('static-value', $closureTwo());
        $this->assertSame($closureOne, $closureTwo);
    }

    /**
     * It should return the same callback instance method on bound singleton
     *
     * @test
     */
    public function should_return_the_same_callback_instance_method_on_bound_singleton()
    {
        $container = new Container();
        $container->singleton(CallbackClassOne::class);

        $closureOne = $container->callback(CallbackClassOne::class, 'getInstanceValue');
        $closureTwo = $container->callback(CallbackClassOne::class, 'getInstanceValue');

        $this->assertSame($closureOne, $closureTwo);
        $this->assertEquals('instance-value', $closureOne());
        $this->assertEquals('instance-value', $closureTwo());
    }

    /**
     * It should return the same callback static method on bound singleton
     *
     * @test
     */
    public function should_return_the_same_callback_static_method_on_bound_singleton()
    {
        $container = new Container();
        $container->singleton(CallbackClassOne::class);

        $closureOne = $container->callback(CallbackClassOne::class, 'getStaticValue');
        $closureTwo = $container->callback(CallbackClassOne::class, 'getStaticValue');

        $this->assertSame($closureOne, $closureTwo);
        $this->assertEquals('static-value', $closureOne());
        $this->assertEquals('static-value', $closureTwo());
    }
}
