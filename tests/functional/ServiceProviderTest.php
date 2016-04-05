<?php
include_once dirname(__FILE__) . '../../data/ServiceProviderOne.php';

class ServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should throw if trying to register a non existing class as provider
     */
    public function it_should_throw_if_trying_to_register_a_non_existing_class_as_provider()
    {
        $container = new tad_DI52_Container();

        $this->setExpectedException('InvalidArgumentException');

        $container->register('NotAServiceProvider');
    }

    /**
     * @test
     * it should throw if trying to register a class that's not extending the service provider class
     */
    public function it_should_throw_if_trying_to_register_a_class_that_s_not_extending_the_service_provider_class()
    {
        $container = new tad_DI52_Container();

        $this->setExpectedException('InvalidArgumentException');

        $container->register('ClassOne');
    }

    /**
     * @test
     * it should allow a service provider to access previous bindings
     */
    public function it_should_allow_a_service_provider_to_access_previous_bindings()
    {
        $container = new tad_DI52_Container();

        $container->register('ServiceProviderOne');

        $out = $container->make('TestInterfaceThree');

        $this->assertInstanceOf('TestInterfaceThree', $out);
    }

    /**
     * @test
     * it should allow a service provider to bind at boot
     */
    public function it_should_allow_a_service_provider_to_bind_at_boot()
    {
        $container = new tad_DI52_Container();

        $container->register('ServiceProviderTwo');

        $container->boot();

        $out = $container->make('TestInterfaceThree');

        $this->assertInstanceOf('TestInterfaceThree', $out);
    }
}
