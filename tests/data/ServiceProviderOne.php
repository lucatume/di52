<?php

class ServiceProviderOne extends tad_DI52_ServiceProvider
{

    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->bind('TestInterfaceOne', 'ClassOne');
        $this->container->bind('TestInterfaceTwo', 'ClassTwo');
        $this->container->bind('TestInterfaceThree', 'ClassThree');
    }

    /**
     * Binds and sets up implementations at boot time.
     */
    public function boot()
    {
    }
}