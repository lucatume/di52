<?php


class tad_DI52_Container implements ArrayAccess, tad_DI52_ContainerInterface
{

    /**
     * @var array
     */
    protected $protected = array();

    /**
     * @var array
     */
    protected $strings = array();

    /**
     * @var array
     */
    protected $objects = array();

    /**
     * @var array
     */
    protected $callables = array();

    /**
     * @var array
     */
    protected $singletons = array();

    /**
     * @var array
     */
    protected $deferred = array();

    /**
     * @var array
     */
    protected $chains = array();

    /**
     * @var array
     */
    protected $reflections = array();

    /**
     * @var array
     */
    protected $parameterReflections = array();

    /**
     * @var array
     */
    protected $afterbuild = array();

    /**
     * @var string
     */
    protected $resolving = '';

    /**
     * @var array
     */
    protected $tags = array();

    /**
     * @var array
     */
    protected $bootable = array();

    /**
     * @var array
     */
    protected $contexts = array();

    /**
     * @var string
     */
    protected $bindingFor;

    /**
     * @var string
     */
    protected $neededImplementation;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setVar($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if ($value instanceof tad_DI52_ProtectedValue) {
            $this->protected[$offset] = true;
            $value = $value->getValue();
        }

        $this->singletons[$offset] = $offset;

        if (isset($this->protected[$offset])) {
            $this->strings[$offset] = $value;
        }

        if (is_object($value)) {
            $this->objects[$offset] = $value;
        }

        if (is_callable($value)) {
            $this->callables[$offset] = $value;
        }

        $this->strings[$offset] = $value;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        return $this->offsetGet($key);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if (is_object($offset)) {
            return is_callable($offset) ? call_user_func($offset, $this) : $offset;
        }

        if (isset($this->objects[$offset])) {
            return $this->objects[$offset];
        }

        if (isset($this->strings[$offset])) {
            if (class_exists($this->strings[$offset])) {
                $instance = $this->make($this->strings[$offset]);
                $this->objects[$offset] = $instance;
                return $instance;
            }
            return $this->strings[$offset];
        }

        if (isset($this->callables[$offset])) {
            return call_user_func($this->callables[$offset]);
        }

        if (isset($this->singletons[$offset])) {
            $this->objects[$offset] = call_user_func($this->singletons[$offset]);
            return $this->objects[$offset];
        }

        if (class_exists($offset)) {
            return $this->resolve($offset);
        }

        throw new RuntimeException("Nothing is bound to the key '{$offset}'");
    }

    /**
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function make($classOrInterface)
    {
        if (isset($this->objects[$classOrInterface])) {
            return $this->objects[$classOrInterface];
        }

        $resolved = $this->resolve($classOrInterface);

        if (isset($this->singletons[$classOrInterface])) {
            $this->objects[$classOrInterface] = $resolved;
        }

        return $resolved;
    }

    /**
     * @param string $classOrInterface
     * @return array|mixed
     */
    protected function resolve($classOrInterface)
    {
        $original = $this->resolving;
        $this->resolving = $classOrInterface;

        try {
            if (isset($this->deferred[$classOrInterface])) {
                /** @var tad_DI52_ServiceProviderInterface $provider */
                $provider = $this->deferred[$classOrInterface];
                $provider->register();
            }

            if (!isset($this->strings[$classOrInterface])) {
                if (!class_exists($classOrInterface)) {
                    throw new RuntimeException("'{$classOrInterface}' is not a bound alias or an existing class.");
                }

                $instance = $this->build($classOrInterface);
            } else {
                if (is_object($this->strings[$classOrInterface]) && !is_callable($this->strings[$classOrInterface])) {
                    $instance = $this->strings[$classOrInterface];
                } elseif (is_callable($this->strings[$classOrInterface])) {
                    $instance = $this->buildFromCallable($classOrInterface);
                } elseif (isset($this->chains[$classOrInterface])) {
                    $instance = $this->buildFromChain($classOrInterface);
                } else {
                    $instance = $this->build($this->strings[$classOrInterface]);
                }
            }

            if (isset($this->afterbuild[$classOrInterface])) {
                foreach ($this->afterbuild[$classOrInterface] as $method) {
                    call_user_func(array($instance, $method));
                }
            }

            $this->resolving = $original;

            return $instance;
        } catch (Exception $e) {
            preg_match('/Error while making/', $e->getMessage(), $matches);
            if (count($matches)) {
                $divider = "\n\t => ";
                $prefix = ' ';
            } else {
                $divider = ':';
                $prefix = 'Error while making ';
            }
            $message = "{$prefix} '{$classOrInterface}'{$divider} " . $e->getMessage();

            throw new RuntimeException($message);
        }
    }

    /**
     * @param $implementation
     * @return mixed
     */
    protected function build($implementation)
    {
        if (!isset($this->reflections[$implementation])) {
            $this->reflections[$implementation] = new ReflectionClass($implementation);
        }

        if (!isset($this->parameterReflections[$implementation])) {
            /** @var ReflectionClass $classReflection */
            $classReflection = $this->reflections[$implementation];
            $constructor = $classReflection->getConstructor();
            $parameters = empty($constructor) ? array() : $constructor->getParameters();
            $this->parameterReflections[$implementation] = array_map(array($this, 'getParameter'), $parameters);
        }

        $instance = !empty($this->parameterReflections[$implementation]) ?
            $this->reflections[$implementation]->newInstanceArgs($this->parameterReflections[$implementation])
            : new $implementation;

        return $instance;
    }

    /**
     * @param $classOrInterface
     * @return mixed
     */
    protected function buildFromCallable($classOrInterface)
    {
        $instance = call_user_func($this->strings[$classOrInterface], $this);
        return $instance;
    }

    /**
     * @param string $classOrInterface
     * @return mixed
     */
    protected function buildFromChain($classOrInterface)
    {
        $chainElements = $this->chains[$classOrInterface];
        unset($this->chains[$classOrInterface]);

        $instance = null;
        foreach (array_reverse($chainElements) as $element) {
            $instance = $this->resolve($element);
            $this->objects[$classOrInterface] = $instance;
        }

        $this->chains[$classOrInterface] = $chainElements;
        unset($this->objects[$classOrInterface]);

        return $instance;
    }

    /**
     * Binds an interface or class to an implementation and will always return the same instance.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function singleton($classOrInterface, $implementation, array $afterBuildMethods = null)
    {
        $this->singletons[$classOrInterface] = $classOrInterface;
        $this->bind($classOrInterface, $implementation, $afterBuildMethods);
    }

    /**
     * Binds an interface or class to an implementation.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function bind($classOrInterface, $implementation, array $afterBuildMethods = null)
    {
        $this->strings[$classOrInterface] = $implementation;

        if (!empty($afterBuildMethods)) {
            $this->afterbuild[$classOrInterface] = $afterBuildMethods;
        }
    }

    /**
     * Tags an array of implementation bindings.
     *
     * @param array $implementationsArray
     * @param string $tag
     */
    public function tag(array $implementationsArray, $tag)
    {
        $this->tags[$tag] = $implementationsArray;
    }

    /**
     * Retrieves an array of bound implementations resolving them.
     *
     * @param string $tag
     * @return array An array of resolved bound implementations.
     */
    public function tagged($tag)
    {
        if ($this->hasTag($tag)) {
            return array_map(array($this, 'offsetGet'), $this->tags[$tag]);
        }

        throw new RuntimeException("Nothing has been tagged {$tag}.");
    }

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag)
    {
        return isset($this->tags[$tag]);
    }

    /**
     * Registers a service provider implementation.
     *
     * @param string $serviceProviderClass
     */
    public function register($serviceProviderClass)
    {
        /** @var tad_DI52_ServiceProviderInterface $provider */
        $provider = new $serviceProviderClass($this);
        if (!$provider->isDeferred()) {
            $provider->register();
        } else {
            $provided = $provider->provides();
            $count = count($provided);
            if ($count === 0) {
                throw new RuntimeException("Service provider '{$serviceProviderClass}' is marked as deferred but is not providing any implementation.");
            }
            $this->deferred = array_merge($this->deferred,
                array_combine($provided, array_fill(0, $count, $provider)));
        }
        $ref = new \ReflectionMethod($provider, 'boot');
        $requiresBoot = ($ref->getDeclaringClass()->getName() === get_class($provider));
        if ($requiresBoot) {
            $this->bootable[] = $provider;
        }
    }

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     */
    public function boot()
    {
        if (empty($this->bootable)) {
            return;
        }

        foreach ($this->bootable as $provider) {
            /** @var tad_DI52_ServiceProviderInterface $provider */
            $provider->boot();
        }
    }

    /**
     * Checks whether if an interface or class has been bound to a concrete implementation.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function isBound($classOrInterface)
    {
        return $this->offsetExists($classOrInterface);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->strings[$offset]) || isset($this->objects[$offset]);
    }

    /**
     * Binds a chain of decorators to a class or interface to be returned as a singleton.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function singletonDecorators($classOrInterface, $decorators)
    {
        $this->bindDecorators($classOrInterface, $decorators);
        $this->singletons[$classOrInterface] = $classOrInterface;
    }

    /**
     * Binds a chain of decorators to a class or interface.
     *
     * The base decorated class must be the last one in the array.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function bindDecorators($classOrInterface, array $decorators)
    {
        $this->strings[$classOrInterface] = $decorators;
        $this->chains[$classOrInterface] = $decorators;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->strings[$offset], $this->objects[$offset]);
    }

    /**
     * Starts the `when->needs->give` chain for a contextual binding.
     *
     * @param string $class The fully qualified name of the requesting class.
     *
     * Example:
     *
     *      // any class requesting an implementation of `LoggerInterface` will receive this implementation...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // but if the requesting class is `Worker` return another implementation
     *      $container->when('Worker')
     *          ->needs('LoggerInterface)
     *          ->give('RemoteLogger);
     *
     * @return tad_DI52_ContainerInterface
     */
    public function when($class)
    {
        $this->bindingFor = $class;

        return $this;
    }

    /**
     * @param string $classOrInterface The class or interface needed by the class.
     *
     * @return tad_DI52_Container
     */
    public function needs($classOrInterface)
    {
        $this->neededImplementation = $classOrInterface;
        return $this;
    }

    /**
     * @param mixed $implementation The implementation specified
     */
    public function give($implementation)
    {
        $this->contexts[$this->neededImplementation] =
            !empty($this->contexts[$this->neededImplementation]) ?
                $this->contexts[$this->neededImplementation] : array();
        $this->contexts[$this->neededImplementation][$this->bindingFor] = $implementation;
    }

    /**
     * Protects a value from being resolved by the container.
     *
     * Example usage `$container['var'] = $container->protect(function(){return 'bar';});`
     *
     * @param mixed $value
     */
    public function protect($value)
    {
        return new tad_DI52_ProtectedValue($value);
    }

    protected function getParameter(ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        if (null === $class) {
            if (!$parameter->isDefaultValueAvailable()) {
                throw new RuntimeException("parameter '{$parameter->name}' of '{$this->resolving}::__construct' does not have a default value.");
            }
            return $parameter->getDefaultValue();
        }

        $parameterClass = $parameter->getClass()->getName();

        return isset($this->contexts[$parameterClass][$this->resolving]) ?
            $this->offsetGet($this->contexts[$parameterClass][$this->resolving])
            : $this->offsetGet($parameterClass);
    }
}
