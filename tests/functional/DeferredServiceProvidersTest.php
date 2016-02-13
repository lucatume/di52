<?php

class DeferredServiceProvidersTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        global $_flag;
        $_flag = false;
        parent::setUp();
    }

    /**
     * @test
     * it should not instantiate a deferred service provider if provided class is not resolved
     */
    public function it_should_not_instantiate_a_deferred_service_provider_if_provided_class_is_not_resolved()
    {
        $container = new tad_DI52_Container();

        $container->register('DeferredServiceProviderOne');

        global $_flag;
        $this->assertFalse($_flag);
    }

    /**
     * @test
     * it should instantiate a deferred service provider when resolving a class it provides
     */
    public function it_should_instantiate_a_deferred_service_provider_when_resolving_a_class_it_provides()
    {
        $container = new tad_DI52_Container();

        $container->register('DeferredServiceProviderOne');

        $container->resolve('TestInterfaceOne');

        global $_flag;
        $this->assertTrue($_flag);
    }

    /**
     * @test
     * it should allow a deferred service provider to bind a singleton
     */
    public function it_should_allow_a_deferred_service_provider_to_bind_a_singleton()
    {
        $container = new tad_DI52_Container();

        $container->register('DeferredServiceProviderTwo');

        $instance1 = $container->resolve('TestInterfaceOne');
        $instance2 = $container->resolve('TestInterfaceOne');

        $this->assertSame($instance1, $instance2);
    }

    /**
     * @test
     * it should allow a deferred service provider to bind and singleton classes
     */
    public function it_should_allow_a_deferred_service_provider_to_bind_and_singleton_classes()
    {
        $container = new tad_DI52_Container();

        $container->register('DeferredServiceProviderThree');

        $instance1 = $container->resolve('TestInterfaceOne');
        $instance2 = $container->resolve('TestInterfaceOne');

        $this->assertSame($instance1, $instance2);

        $instance3 = $container->resolve('TestInterfaceTwo');
        $instance4 = $container->resolve('TestInterfaceTwo');

        $this->assertEquals($instance3, $instance4);
        $this->assertNotSame($instance3, $instance4);
    }
}
