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
     * @var array
     */
    protected $deferredServiceProviders = array();

    /**
     * @var array
     */
    protected $singletonAliases = array();

    /**
     * @var array
     */
    protected $singletonImplementations = array();

    /**
     * @var array
     */
    protected $singletonImplementationObjects = array();

    /**
     * @var array
     */
    protected $decoratorsChain = array();

    /**
     * @var bool
     */
    protected $resolvingDecorator = false;

    /**
     * @var array
     */
    protected $customBindings = array();

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
     * @param string $classOrInterface
     * @param string $implementation
     * @param bool $skipImplementationCheck Whether the implementation should be checked as valid implementation or
     * extension of the class.
     */
    public function bind($classOrInterface, $implementation, $skipImplementationCheck = false)
    {
        $this->_bind($classOrInterface, $implementation, $skipImplementationCheck);
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
        $this->_bind($interfaceOrClass, $implementation, $skipImplementationCheck, true);
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
        if (!isset($this->tagged[$tag])) {
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

        if ($serviceProvider->isDeferred()) {
            $providedClasses = $serviceProvider->provides();
            $buffer = array_combine($providedClasses, array_fill(0, count($providedClasses), $serviceProvider));
            $this->deferredServiceProviders = array_merge($this->deferredServiceProviders, $buffer);
        } else {
            $serviceProvider->register();
        }

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

    /**
     * Checks whether if an interface or class has been bound to a concrete implementation.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function isBound($classOrInterface)
    {
        if (!is_string($classOrInterface)) {
            throw new InvalidArgumentException('Class or interface must be a string');
        }

        return isset($this->bindings[$classOrInterface]);
    }

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag)
    {
        if (!is_string($tag)) {
            throw new InvalidArgumentException('Tag must be a string');
        }

        return isset($this->tagged[$tag]);
    }


    /**
     * Binds a chain of decorators to a class or interface.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function bindDecorators($classOrInterface, array $decorators)
    {
        $this->bind($classOrInterface, end($decorators));
        $this->decoratorsChain[$classOrInterface] = $decorators;
    }

    /**
     * Binds a chain of decorators to a class or interface to be returned as a singleton.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function singletonDecorators($classOrInterface, $decorators)
    {
        $this->singleton($classOrInterface, end($decorators));
        $this->decoratorsChain[$classOrInterface] = $decorators;
    }

    /**
     * Binds a class or interface implementation to a specific class resolution.
     * When resolving `customClass` requests for the `classOrInterface` will be bound to `implementation`.
     *
     * @param string $customClass
     * @param string $classOrInterface
     * @param mixed $implementation
     *
     * @return mixed
     */
    public function bindFor($customClass, $classOrInterface, $implementation)
    {
        if (empty($this->customBindings[$customClass])) {
            $this->customBindings[$customClass] = array();
        }
        $this->customBindings[$customClass][$classOrInterface] = $implementation;
    }

    protected function bootServiceProvider(tad_DI52_ServiceProviderInterface $serviceProvider)
    {
        return $serviceProvider->boot();
    }

    /**
     * @param $classOrInterface
     * @param $implementation
     * @param $skipImplementationCheck
     */
    protected function _bind($classOrInterface, $implementation, $skipImplementationCheck, $isSingleton = false)
    {
        $interfaceExists = interface_exists($classOrInterface);
        $classExists = class_exists($classOrInterface);
        $isCallbackImplementation = is_callable($implementation);
        $isInstanceImplementation = is_object($implementation);

        $implementation_object = null;

        if ($isSingleton && $index = array_search($implementation, $this->singletonImplementations)) {
            $implementation_object = $this->singletonImplementationObjects[$index];
        } elseif (is_string($implementation)) {
            if (!(class_exists($implementation))) {
                throw new InvalidArgumentException("Implementation class [{$implementation}] does not exist.");
            }
            if (!$skipImplementationCheck) {
                if ($interfaceExists && !in_array($classOrInterface, class_implements($implementation))) {
                    throw new InvalidArgumentException("Implementation class [{$implementation}] should implement interface [{$classOrInterface}].");
                } elseif ($classExists && !(in_array($classOrInterface, class_parents($implementation)) || $implementation === $classOrInterface)) {
                    throw new InvalidArgumentException("Implementation class [{$implementation}] should extend class [{$classOrInterface}].");
                }
            }
            $implementation_object = new tad_DI52_Bindings_ConstructorImplementation($implementation, $this->container, $this);
        } elseif ($isCallbackImplementation) {
            $implementation_object = new tad_DI52_Bindings_CallbackImplementation($implementation, $this->container, $this);
        } elseif ($isInstanceImplementation) {
            $implementation_object = new tad_DI52_Bindings_InstanceImplementation($implementation, $this->container, $this);
        } else {
            throw new InvalidArgumentException("Implementation should be a class name, a callback or an object instance.");
        }

        $this->bindings[$classOrInterface] = $implementation_object;

        if ($isSingleton) {
            if (empty($index)) {
                $index = microtime();
                $this->singletonImplementations[$index] = $implementation;
                $this->singletonImplementationObjects[$index] = $implementation_object;
            }
            $this->singletonAliases[$classOrInterface] = $index;
        }
    }

    /**
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function resolve($classOrInterface)
    {
        $isDeferredBound = isset($this->deferredServiceProviders[$classOrInterface]);
        if ($isDeferredBound) {
            $serviceProvider = $this->deferredServiceProviders[$classOrInterface];
            $serviceProvider->register();
        }

        $isCustomBound = array_key_exists($classOrInterface, $this->customBindings);
        $isBound = $isCustomBound || isset($this->bindings[$classOrInterface]);
        $isDecoratorChain = !$this->resolvingDecorator && isset($this->decoratorsChain[$classOrInterface]);

        $isSingleton = in_array($classOrInterface, $this->singletons);
        if (!$isBound) {
            $resolved = $this->resolveUnbound($classOrInterface);
            return $resolved;
        }

        if ($isSingleton && isset($this->resolvedSingletons[$classOrInterface])) {
            return $this->resolvedSingletons[$classOrInterface];
        }

        if ($isCustomBound) {
            $customImplementations = $this->customBindings[$classOrInterface];
            $subResolver = clone $this;
            $subResolver->resetCustomBindings();
            foreach ($customImplementations as $_classOrInterface => $_implementation) {
                $subResolver->bind($_classOrInterface, $_implementation);
            }
            $resolved = $subResolver->resolve($classOrInterface);
        } else {
            $resolved = $isDecoratorChain ? $this->resolveBoundDecoratorChain($classOrInterface) : $this->resolveBound($classOrInterface);
        }

        if ($isSingleton) {
            $this->resolvedSingletons[$classOrInterface] = $resolved;
            $index = $this->singletonAliases[$classOrInterface];
            foreach ($this->singletonAliases as $alias => $aliasIndex) {
                if ($alias !== $classOrInterface && $aliasIndex === $index) {
                    $this->resolvedSingletons[$alias] = $resolved;
                }
            }
        }

        return $resolved;
    }

    private function getDependencies($parameters)
    {
        $dependencies = array();

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if ($dependency === null) {
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
        $reflector = new ReflectionClass($classOrInterface);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("[{$classOrInterface}] is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $classOrInterface;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveBound($classOrInterface)
    {
        $implementation = $this->bindings[$classOrInterface];
        return $implementation->getImplementation() === $classOrInterface ? $this->resolveUnbound($classOrInterface) : $implementation->instance();
    }

    protected function resolveBoundDecoratorChain($classOrInterface)
    {
        $chain = $this->decoratorsChain[$classOrInterface];
        $base = array_pop($chain);
        $this->bind($classOrInterface, $base);
        $resolvedDecorator = null;

        $this->resolvingDecorator = true;

        foreach (array_reverse($chain) as $decorator) {
            $resolvedDecorator = $this->resolveUnbound($decorator);
            $this->bind($classOrInterface, $resolvedDecorator);
        }

        $this->resolvingDecorator = false;

        return $resolvedDecorator;
    }

    protected function resetCustomBindings()
    {
        $this->customBindings = array();
    }
}
