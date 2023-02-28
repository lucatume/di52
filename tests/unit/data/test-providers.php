<?php

use lucatume\DI52\ServiceProvider as Provider;

class ProviderOne extends Provider
{
    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->bind('foo', 23);
    }
}

class DeferredProviderOne extends Provider
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

class DeferredProviderTwo extends Provider
{

    protected static $wasRegistered = false;
    protected $deferred = true;

    public static function wasRegistered()
    {
        return self::$wasRegistered;
    }

    public static function reset()
    {
        self::$wasRegistered = false;
    }

    public function provides()
    {
        return [ 'One' ];
    }


    /**
     * Binds and sets up implementations.
     */
    public function register()
    {
        $this->container->bind('One', 'ClassOne');
        self::$wasRegistered = true;
    }
}

class ProviderThree extends Provider
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
