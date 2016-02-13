<?php

class DeferredServiceProviderOne extends tad_DI52_ServiceProvider
{
    protected $deferred = true;

    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        global $_flag;
        $_flag = true;

        $this->container->bind('TestInterfaceOne', 'ClassOne');

    }

    /**
     * Binds and sets up implementations at boot time.
     */
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    public function provides()
    {
        return array(
            'TestInterfaceOne',
        );
    }
}