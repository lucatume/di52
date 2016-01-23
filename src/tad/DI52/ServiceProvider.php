<?php

abstract class tad_DI52_ServiceProvider implements tad_DI52_ServiceProviderInterface
{
    /**
     * @var tad_DI52_Container
     */
    protected $container;

    /**
     * tad_DI52_ServiceProvider constructor.
     * @param tad_DI52_Container $container
     */
    public function __construct(tad_DI52_Container $container)
    {
        $this->container = $container;
    }

    /**
     * Binds and sets up implementations.
     */
    abstract public function register();

    /**
     * Binds and sets up implementations at boot time.
     */
    abstract public function boot();
}