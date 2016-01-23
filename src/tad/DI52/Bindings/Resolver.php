<?php

class tad_DI52_Bindings_Resolver implements tad_DI52_Bindings_ResolverInterface
{
    /**
     * @var tad_DI52_Bindings_ImplementationCallback[]
     */
    protected $bindings = array();

    /**
     * @var array
     */
    protected $singletons = array();

    /**
     * @var array
     */
    protected $resolvedSingletons = array();

    /**
     * @var array
     */
    protected $tagged = array();

    /**
     * @var tad_DI52_ServiceProviderInterface[]
     */
    protected $serviceProviders = array();

    /**
     * @var tad_DI52_Container
     */
    private $container;

    /**
     * @param tad_DI52_Container $container
     */
    public function __construct(tad_DI52_Container $container)
    {
        $this->container = $container;
    }

    /**
     * Binds an interface or class to an implementation.
     *
     * @param string $interfaceOrClass
     * @param string $implementation
     * @param bool $skipImplementationCheck Whether the implementation should be checked as valid implementation or
     * extension of the class.
     */
    public function bind($interfaceOrClass, $implementation, $skipImplementationCheck = false)
    {
        $interfaceExists = interface_exists($interfaceOrClass);
        $classExists = class_exists($interfaceOrClass);
        $isCallbackImplementation = is_callable($implementation);
        $isInstanceImplementation = is_object($implementation);

        if (!($interfaceExists || $classExists)) {
            throw new InvalidArgumentException("Class or interface [{$interfaceOrClass}] does not exist.");
        }
        if (is_string($implementation)) {
            if (!(class_exists($implementation))) {
                throw new InvalidArgumentException("Implementation class [{$implementation}] does not exist.");
            }
            if (!$skipImplementationCheck) {
                if ($interfaceExists && !in_array($interfaceOrClass, class_implements($implementation))) {
                    throw new InvalidArgumentException("Implementation class [{$implementation}] should implement interface [{$interfaceOrClass}].");
                } elseif ($classExists && !in_array($interfaceOrClass, class_parents($implementation))) {
                    throw new InvalidArgumentException("Implementation class [{$implementation}] should extend class [{$interfaceOrClass}].");
                }
            }
            $this->bindings[$interfaceOrClass] = new tad_DI52_Bindings_ConstructorImplementation($implementation, $this->container, $this);
        } elseif ($isCallbackImplementation) {
            $this->bindings[$interfaceOrClass] = new tad_DI52_Bindings_CallbackImplementation($implementation, $this->container, $this);
        } elseif ($isInstanceImplementation) {
            $this->bindings[$interfaceOrClass] = new tad_DI52_Bindings_InstanceImplementation($implementation, $this->container, $this);
        } else {
            throw new InvalidArgumentException("Implementation should be a class name, a callback or an object instance.");
        }
    }

    /**
     * Binds an interface or class to an implementation and will always return the same instance.
     *
     * @param string $interfaceOrClass
     * @param string $implementation
     * @param bool $skipImplementationCheck Whether the implementation should be checked as valid implementation or
     * extension of the class.
     */
    public function singleton($interfaceOrClass, $implementation, $skipImplementationCheck = false)
    {
        $this->bind($interfaceOrClass, $implementation, $skipImplementationCheck);
        $this->singletons[] = $interfaceOrClass;
    }

    /**
     * Tags an array of implementation bindings.
     *
     * @param array $implementationsArray
     * @param string $tag
     */
    public function tag(array $implementationsArray, $tag)
    {
        if (!is_string($tag)) {
            throw new InvalidArgumentException('Tag must be a string.');
        }
        $this->tagged[$tag] = $implementationsArray;
    }

    /**
     * Retrieves an array of bound implementations resolving them.
     *
     * @param string $tag
     * @return array An array of resolved bound implementations.
     */
    public function tagged($tag)
    {
        if (!is_string($tag)) {
            throw new InvalidArgumentException('Tag must be a string.');
        }
        if (!array_key_exists($tag, $this->tagged)) {
            throw new InvalidArgumentException("No implementations array was tagged [$tag]");
        }

        return array_map(array($this, 'resolve'), $this->tagged[$tag]);
    }

    /**
     * Registers a service provider implementation.
     *
     * @param string $serviceProviderClass
     */
    public function register($serviceProviderClass)
    {
        if (!class_exists($serviceProviderClass)) {
            throw new InvalidArgumentException("Service provider class [{$serviceProviderClass}] does not exist.");
        }
        if (!in_array('tad_DI52_ServiceProviderInterface', class_implements($serviceProviderClass))) {
            throw new InvalidArgumentException("Service provider class [{$serviceProviderClass}] is not an implementation of the [tad_DI52_ServiceProviderInterface] interface.");
        }

        /** @var tad_DI52_ServiceProviderInterface $serviceProvider */
        $serviceProvider = new $serviceProviderClass($this->container);
        $this->serviceProviders[] = $serviceProvider;
        $serviceProvider->register();

        return $serviceProvider;
    }

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     */
    public function boot()
    {
        if (empty($this->serviceProviders)) {
            return;
        }
        return array_map(array($this, 'bootServiceProvider'), $this->serviceProviders);
    }

    protected function bootServiceProvider(tad_DI52_ServiceProviderInterface $serviceProvider)
    {
        return $serviceProvider->boot();
    }

    /**
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function resolve($classOrInterface)
    {
        $isClass = class_exists($classOrInterface);
        $isInterface = interface_exists($classOrInterface);
        $isSingleton = in_array($classOrInterface, $this->singletons);
        if (!($isInterface || $isClass)) {
            throw new InvalidArgumentException("[{$classOrInterface}] does not exist");
        }
        $isBound = array_key_exists($classOrInterface, $this->bindings);
        if (!$isBound) {
            $resolved = $this->resolveUnbound($classOrInterface);
            if ($isSingleton) {
                if (array_key_exists($classOrInterface, $this->resolvedSingletons)) {
                    return $this->resolvedSingletons[$classOrInterface];
                }
                $this->resolvedSingletons[$classOrInterface] = $resolved;
            }
            return $resolved;
        }

        $resolved = $this->resolveBound($classOrInterface);
        if ($isSingleton) {
            if (array_key_exists($classOrInterface, $this->resolvedSingletons)) {
                return $this->resolvedSingletons[$classOrInterface];
            }
            $this->resolvedSingletons[$classOrInterface] = $resolved;
        }
        return $resolved;
    }

    private function getDependencies($parameters)
    {
        $dependencies = array();

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if (is_null($dependency)) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolve($dependency->name);
            }
        }

        return $dependencies;
    }

    private function resolveNonClass($parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new InvalidArgumentException("Erm.. Cannot resolve the unkown!?");
    }

    private function resolveUnbound($classOrInterface)
    {
        $isClass = class_exists($classOrInterface);
        $isInterface = interface_exists($classOrInterface);
        if ($isInterface) {
            throw new InvalidArgumentException("Interface [{$classOrInterface}] is not bound to any implementation.");
        }
        $reflector = new ReflectionClass($classOrInterface);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("[{$classOrInterface}] is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $classOrInterface;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveBound($classOrInterface)
    {
        $implementation = $this->bindings[$classOrInterface];
        return $implementation->instance();
    }
}