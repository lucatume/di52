<?php

class tad_DI52_Bindings_CallbackImplementation implements tad_DI52_Bindings_ImplementationInterface
{
    /**
     * @var callable
     */
    private $implementation;
    /**
     * @var tad_DI52_Container
     */
    private $container;

    /**
     * @var tad_DI52_Bindings_ResolverInterface
     */
    private $resolver;

    /**
     * tad_DI52_Bindings_ConstructorImplementation constructor.
     * @param string $implementation
     * @param tad_DI52_Container $container
     * @param tad_DI52_Bindings_ResolverInterface $resolver
     */
    public function __construct($implementation, tad_DI52_Container $container, tad_DI52_Bindings_ResolverInterface $resolver)
    {
        $this->implementation = $implementation;
        $this->container = $container;
        $this->resolver = $resolver;
    }


    /**
     * Returns an object instance.
     *
     * @return mixed
     */
    public function instance()
    {
        if (is_callable($this->implementation)) {
            return $this->implementation = call_user_func($this->implementation, $this->container);
        }

        return $this->implementation;
    }
}