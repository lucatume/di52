<?php

class ProviderOne extends tad_DI52_ServiceProvider
{
    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->bind('foo', 23);
    }
}

class DeferredProviderOne extends tad_DI52_ServiceProvider
{

    protected $deferred = true;

    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->bind('foo', 23);
    }

}

class DeferredProviderTwo extends tad_DI52_ServiceProvider
{

    protected $deferred = true;

    public function provides()
    {
        return array('One');
    }


    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->bind('One', 'ClassOne');
    }

}

class ProviderThree extends tad_DI52_ServiceProvider
{

    /**
     * Binds and sets up implementations.
     */
    public function boot()
    {
        $this->container->bind('One', 'ClassOne');
    }

    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        // no-op
    }
}
