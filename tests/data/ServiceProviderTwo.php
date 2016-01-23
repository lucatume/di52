<?php

class ServiceProviderTwo extends tad_DI52_ServiceProvider
{

    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
    }

    /**
     * Binds and sets up implementations at boot time.
     */
    public function boot()
    {
        $this->container->bind('TestInterfaceOne', 'ClassOne');
        $this->container->bind('TestInterfaceTwo', 'ClassTwo');
        $this->container->bind('TestInterfaceThree', 'ClassThree');
    }
}