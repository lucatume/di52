<?php
/**
 * The Dependency Injection container.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52;

use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class Container
 *
 * @since   TBD
 *
 * @package lucatume\DI52
 * @implements \ArrayAccess<string,object>
 */
class Container implements \ArrayAccess, ContainerInterface
{
    const CLASS_EXISTS = 0b0001;
    const CLASS_IS_INSTANTIATABLE = 0b00010;

    /**
     * An array cache to store the results of the class exists checks.
     *
     * @var array<string,bool>
     */
    protected static $classExistsCache = [];

    /**
     * A cache of what methods are static and what are not.
     *
     * @var array<string,bool>
     */
    protected static $isStaticMethodCache = [];

    /**
     * Whether unbound classes should be resolved as singletons, by default, or not.
     *
     * @var bool
     */
    protected $resolveUnboundAsSingletons = false;
    /**
     * A list of bound and resolved singletons.
     *
     * @var array<string,bool>
     */
    protected $singletons = [];
    /**
     * @var array<ServiceProvider>
     */
    protected $deferred = [];
    /**
     * @var array<string,ReflectionClass<object>>
     */
    protected $reflections = [];
    /**
     * @var array<string,array<string|object|callable>>
     */
    protected $tags = [];
    /**
     * @var array<ServiceProvider>
     */
    protected $bootable = [];
    /**
     * @var string
     */
    protected $whenClass;
    /**
     * @var string
     */
    protected $needsClass;
    /**
     * A map from classes to what actual class to build when they need it.
     * Stores the results of the when > needs > give calls.
     *
     * @var array<string,array<string,string>>
     */
    protected $whenNeedsGive = [];
    /**
     * A map from the bound implementations to either the callables that will build the implementations or the solved
     * singleton implementations.
     * @var array<string,callable|ServiceProvider|object>
     */
    protected $bindings = [];
    /**
     * A map from class name and static methods to the built callback.
     *
     * @var array<string,Closure>
     */
    protected $callbacks = [];
    /**
     * A property to keep track of the current line of make attempt.
     *
     * @var array<string>
     */
    protected $makeLine = [];

    /**
     * Container constructor.
     *
     * @param false $resolveUnboundAsSingletons Whether unbound classes should be resolved as singletons by default,
     *                                          or not.
     */
    public function __construct($resolveUnboundAsSingletons = false)
    {
        $this->resolveUnboundAsSingletons = $resolveUnboundAsSingletons;
    }

    /**
     * Sets a variable on the container.
     *
     * @param string $key   The alias the container will use to reference the variable.
     * @param mixed  $value The variable value.
     *
     * @return void The method does not return any value.
     */
    public function setVar($key, $value)
    {
        $this->bindings[$key] = ProtectedValue::of($value);
    }

    /**
     * Sets a variable on the container using the ArrayAccess API.
     *
     * When using the container as an array bindings will be bound as singletons.
     * These are equivalent: `$container->singleton('foo','ClassOne');`, `$container['foo'] = 'ClassOne';`.
     *
     * @param string $offset The alias the container will use to reference the variable.
     * @param mixed  $value  The variable value.
     *
     * @return void This method does not return any value.
     *
     * @throws ContainerException If the closure building fails.
     */
    public function offsetSet($offset, $value)
    {
        $this->singletons[$offset] = true;
        $this->bindings[$offset] = $this->buildClosure($offset, $value);
    }

    /**
     * Builds and returns a Closure to build and object instance.
     *
     * @param string                                    $id                The id, class or object to build
     *                                                                     the callable for.
     * @param callable|string|array<string>|object|null $implementation    The implementation to build the Closure for.
     * @param array<string>|null                        $afterBuildMethods A set of methods that should be called on the
     *                                                                     instance
     *                                                                     after it's built and before it's returned.
     * @param array<mixed> $buildArgs                                      An array of arguments to use when building
     *                                                                     the instance.
     *
     * @return Closure|callable|object A closure to build the implementation or the implementation itself if already
     *                                 built.
     *
     * @throws ContainerException If there's an issue resolving the id, or its dependencies.
     */
    private function buildClosure(
        $id,
        $implementation,
        array $afterBuildMethods = null,
        ...$buildArgs
    ) {
        // Allow single argument binding.
        $implementation = $implementation === null ? $id : $implementation;
        $implementationIsString = is_string($implementation);

        if ($implementationIsString && $implementation === $id
            && !$this->classExists($id, self::CLASS_IS_INSTANTIATABLE)) {
            throw new NotFoundException(
                "nothing is bound to the '{$id}' id and it's not an existing or instantiable class."
            );
        }

        if ($implementation instanceof Closure) {
            // Ready to run.
            return $implementation;
        }

        if (is_object($implementation)) {
            // Already built, just return this.
            return $implementation;
        }

        if (is_string($implementation) && $this->classExists($implementation, self::CLASS_EXISTS)) {
            // @phpstan-ignore-next-line Keep a reference to the closure to keep it alive while solving it.
            $closure = function () use ($id, $implementation, $buildArgs, $afterBuildMethods, &$closure) {
                $buildArgs = $this->resolveBuildArgs($id, $implementation, ...$buildArgs);
                $instance = new $implementation(...$buildArgs);
                foreach ((array)$afterBuildMethods as $method) {
                    $instance->{$method}();
                }

                return $instance;
            };

            return $closure;
        }

        return ProtectedValue::of($implementation);
    }

    /**
     * A wrapper around the `class_exists` function to capture and handle possible fatal errors on PHP 7.0+.
     *
     * @param string $class The class name to check.
     * @param int    $mask  A bitmask to select the `x_exists` checks to make; respectively `class_exists`,
     *                      `interface_exists` and `trait_exists`; by default, checks for all.
     *
     * @return bool Whether the class exists or not.
     * @throws ContainerException If the class has syntax or other errors preventing it from being loaded.
     */
    protected function classExists($class, $mask = 0b0111)
    {
        $cacheKey = $class . $mask;

        if (isset(static::$classExistsCache[$cacheKey])) {
            return static::$classExistsCache[$cacheKey];
        }

        if (PHP_VERSION_ID < 70000) {
            $exists = $this->checkClassExists($class, $mask); // @codeCoverageIgnore
            static::$classExistsCache[$cacheKey] = $exists;
            return $exists;
        }

        // PHP 7.0+ allows handling fatal errors; x_exists will trigger auto-loading, that might result in an error.
        try {
            $exists = $this->checkClassExists($class, $mask);
            static::$classExistsCache[$cacheKey] = $exists;
            return $exists;
        } catch (\Throwable $e) {
            static::$classExistsCache[$cacheKey] = false;
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * Checks a class, interface or trait exists.
     *
     * @param string $class The class, interface or trait to check.
     * @param int    $mask  A bit mask to indicat what checks to run.
     *
     * @return bool Whether the class, interface or trait exists or not.
     * @throws ReflectionException If the class should be checked for concreteness and it does not exist.
     */
    protected function checkClassExists($class, $mask)
    {
        if (self::CLASS_EXISTS & $mask) {
            return class_exists($class);
        }

        if (self::CLASS_IS_INSTANTIATABLE & $mask) {
            $exists = class_exists($class);
            if (!$exists) {
                return false;
            }
            $classReflection = $this->getClassReflection($class);
            if ($classReflection->isAbstract()) {
                return false;
            }
            $constructor = $classReflection->getConstructor();
            if ($constructor === null) {
                return true;
            }
            return $constructor->isPublic();
        }

        return true;
    }

    /**
     * Returns a ReflectionClass instance, built or cached.
     *
     * @param string $className The fully-qualifed class name to return the class reflection for.
     *
     * @return ReflectionClass The built class reflection object.
     *
     * @throws ReflectionException If the class is not a valid one.
     */
    private function getClassReflection($className)
    {
        if (isset($this->reflections[$className])) {
            return $this->reflections[$className];
        }

        // @phpstan-ignore-next-line Throwing here is fine.
        return new ReflectionClass($className);
    }

    /**
     * Parses and resolves the constructor parameters for a class.
     *
     * @param string $id           The id to resolve the build arguments for.
     * @param string $className    The name of the class to resolve the constructor parameters for.
     * @param mixed  ...$readyArgs A set of ready arguments.
     *
     * @return array<mixed> An array of the resolved class constructor parameters.
     * @throws ContainerException If there's an issue reflecting on the class.
     * @throws ReflectionException If there's an issue reflecting on the class to build.
     */
    private function resolveBuildArgs($id, $className, ...$readyArgs)
    {
        $constructor = $this->getClassReflection($className)->getConstructor();

        if ($constructor === null) {
            // No constructor arguments to resolve.
            return [];
        }

        if (!$constructor->isPublic()) {
            throw new ContainerException("The '{$id}' class constructor method is not public.");
        }

        $resolved = [];
        foreach ($constructor->getParameters() as $i => $parameter) {
            if (isset($readyArgs[$i])) {
                if (is_string($readyArgs[$i]) && isset($this->bindings[$readyArgs[$i]])) {
                    $resolved[] = $this->get($readyArgs[$i]);
                } else {
                    $resolved[] = $this->resolve('arg_' . $i, $readyArgs[$i]);
                }
                continue;
            }
            $resolved[] = $this->resolveParameter($parameter, $id);
        }

        return $resolved;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed The entry for an id.
     *
     * @throws ContainerException Error while retrieving the entry.
     */
    public function get($id)
    {
        $this->makeLine = [];
        if (isset($this->bindings[$id])) {
            $bound = $this->bindings[$id];
            // Bound, resolve and return.
            if ($bound instanceof Closure || $bound instanceof ProtectedValue) {
                $instance = $bound($this);
                if (isset($this->singletons[$id])) {
                    $this->bindings[$id] = $instance;
                }
            } else {
                $instance = $this->bindings[$id];
            }

            return $instance;
        }

        try {
            // Unbound, try and resolve it now.
            return $this->resolve($id);
        } catch (\Exception $exception) {
            throw $this->castException($exception, $id);
        }
    }

    /**
     * Makes something with the option of not checking for consistency and making it safely.
     *
     * @param string|mixed      $id             A fully qualified class or interface name or an already built object.
     * @param string|mixed|null $implementation The optional implementation to resolve.
     *
     * @return mixed The value built by the container.
     * @throws ContainerException If the target of the make is a string and is not bound.
     */
    private function resolve($id, $implementation = null)
    {
        $isString = is_string($id);
        $this->makeLine[] = $isString ? "'{$id}'" : "'" . gettype($id) . "'";
        $maker = $this->buildClosure($id, $implementation ?: $id);

        $made = $maker instanceof Closure || $maker instanceof ProtectedValue ? $maker($this) : $maker;

        if ($this->resolveUnboundAsSingletons && $isString) {
            $this->singletons[$id] = true;
            $this->bindings[$id] = $made;
        }

        return $made;
    }

    /**
     * Builds an instance of the exception with a pretty message.
     *
     * @param \Exception    $exception The exception to cast.
     * @param string|object $id        The top identifier the containe was attempting to build, or object.
     *
     * @return ContainerException The cast exception.
     */
    private function castException(\Exception $exception, $id)
    {
        $exceptionClass = $exception instanceof ContainerException ? get_class($exception) : ContainerException::class;
        $exception = new $exceptionClass($this->buildMakeErrorMessage($id, $exception));

        return $exception;
    }

    /**
     * Formats an error message to provide a useful debug message.
     *
     * @param string|object $id The id of what is actually being built or the object that is being built.
     * @param \Exception    $e  The original exception thrown while trying to make the target.
     *
     * @return string The formatted make error message.
     */
    private function buildMakeErrorMessage($id, \Exception $e)
    {
        $idString = is_string($id) ? $id : gettype($id);
        $last = array_pop($this->makeLine) ?: $idString;
        $lastEntry = "Error while making {$last}: " . lcfirst(
            rtrim(
                str_replace('"', '', $e->getMessage()),
                '.'
            )
        ) . '.';
        $frags = array_merge($this->makeLine, [$lastEntry]);

        return implode("\n\t=> ", $frags);
    }

    /**
     * Resolves a parameter reflection to a value, if possible.
     *
     * @param ReflectionParameter $parameter The parameter reflection to try and resolve.
     * @param string              $id        The id to resolve the parameter for.
     *
     * @return mixed The parameter resolved value, or the parameter default value, if available.
     *
     * @throws ContainerException If there's an issue resolving the parameter or reflecting on it.
     * @throws NotFoundException If the parameters maps to a not found binding.
     */
    protected function resolveParameter(ReflectionParameter $parameter, $id)
    {
        $parameterClassName = $this->getParameterClassName($parameter);

        if ($parameterClassName !== null) {
            if (isset($this->whenNeedsGive[$id][$parameterClassName])) {
                $parameterClassName = $this->whenNeedsGive[$id][$parameterClassName];
            }

            return is_string($parameterClassName) && isset($this->bindings[$parameterClassName]) ?
                $this->get($parameterClassName)
                : $this->resolve($parameterClassName);
        }

        if ($parameter->allowsNull()) {
            try {
                return $parameter->getDefaultValue();
            } catch (ReflectionException $e) {
                throw new ContainerException($e->getMessage());
            }
        }
    }

    /**
     * Returns a parameter class name.
     *
     * @param ReflectionParameter $parameter The parameter to get the class for.
     *
     * @return string|null Either the parameter class name, or `null` if the parameter does not have a class.
     * @throws ContainerException If the parameter class is not defined or its auto-loading triggers an errors;
     *                            this might be the case for file that contain syntax errors.
     */
    private function getParameterClassName(ReflectionParameter $parameter)
    {
        /*
         * Get the parameter class without triggering autoload; if this was triggered here, then syntax errors would
         * not be correctly identified.
         */
        $parameterClassString = $parameter->__toString();
        $parameterClass = preg_replace_callback(
            '/^.*\\[\\s*<(required|optional)>\\s*([\\\\\\w_-]*).*].*$/i',
            static function ($m) {
                return isset($m[2]) ? $m[2] : null;
            },
            $parameterClassString
        );

        return $parameterClass ?: null;
    }

    /**
     * Returns a variable stored in the container.
     *
     * If the variable is a binding then the binding will be resolved before returning it.
     *
     * @param string     $key     The alias of the variable or binding to fetch.
     * @param mixed|null $default A default value to return if the variable is not set in the container.
     *
     * @return mixed The variable value or the resolved binding.
     * @throws ContainerException If there's an issue resolving the variable.
     *
     * @see Container::get()
     */
    public function getVar($key, $default = null)
    {
        if (isset($this->bindings[$key])) {
            return $this->get($key);
        }

        return $default;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $offset Identifier of the entry to look for.
     *
     * @return mixed The entry for an id.
     *
     * @return mixed The value for the offset.
     *
     * @throws ContainerException Error while retrieving the entry.
     * @throws NotFoundException  No entry was found for **this** identifier.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Returns an instance of the class or object bound to an interface, class  or string slug if any, else it will try
     * to automagically resolve the object to a usable instance.
     *
     * If the implementation has been bound as singleton using the `singleton` method
     * or the ArrayAccess API then the implementation will be resolved just on the first request.
     *
     * @param string $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed
     * @throws ContainerException If the target of the make is not bound and is not a valid,
     *                                              concrete, class name or there's any issue making the target.
     */
    public function make($id)
    {
        return $this->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `$container[$id]` returning true does not mean that `$container[$id]` will not throw an exception.
     * It does however mean that `$container[$id]` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->bindings[$offset]) || $this->classExists($offset, self::CLASS_IS_INSTANTIATABLE);
    }

    /**
     * Tags an array of implementations bindings for later retrieval.
     *
     * The implementations can also reference interfaces, classes or string slugs.
     * Example:
     *
     *        $container->tag(['Posts', 'Users', 'Comments'], 'endpoints');
     *
     * @param array<string|callable|object> $implementationsArray The ids, class names or objects to apply the tag to.
     * @param string                        $tag                  The tag to apply.
     *
     * @return void This method does not return any value.
     * @see Container::tagged()
     *
     */
    public function tag(array $implementationsArray, $tag)
    {
        $this->tags[$tag] = $implementationsArray;
    }

    /**
     * Retrieves an array of bound implementations resolving them.
     *
     * The array of implementations should be bound using the `tag` method:
     *
     *        $container->tag(['Posts', 'Users', 'Comments'], 'endpoints');
     *        foreach($container->tagged('endpoints') as $endpoint){
     *            $endpoint->register();
     *        }
     *
     * @param string $tag The tag to return the tagged values for.
     *
     * @return array<mixed> An array of resolved bound implementations.
     * @throws NotFoundException If nothing is tagged with the tag.
     * @throws ContainerException If one of the bindings is not of the correct type.
     * @see Container::tag()
     */
    public function tagged($tag)
    {
        if (!$this->hasTag($tag)) {
            throw new NotFoundException("Nothing is tagged as '{$tag}'");
        }

        return array_map(
            function ($id) {
                if (is_string($id)) {
                    return $this->get($id);
                }
                return $this->resolve($id);
            },
            $this->tags[$tag]
        );
    }

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     *
     * @return bool
     * @see Container::tag()
     *
     */
    public function hasTag($tag)
    {
        return isset($this->tags[$tag]);
    }

    /**
     * Registers a service provider implementation.
     *
     * The `register` method will be called immediately on the service provider.
     *
     * If the provider overloads the  `isDeferred` method returning a truthy value then the `register` method will be
     * called only if one of the implementations provided by the provider is requested. The container defines which
     * implementations is offering overloading the `provides` method; the method should return an array of provided
     * implementations.
     *
     * If a provider overloads the `boot` method that method will be called when the `boot` method is called on the
     * container itself.
     *
     * @param string $serviceProviderClass The fully-qualified Service Provider class name.
     * @param string ...$alias             A list of aliases the provider should be registered with.
     * @return void This method does not return any value.
     * @throws ContainerException If the Service Provider is not correctly configured or there's an issue
     *                                     reflecting on it.
     * @see ServiceProvider::register()
     * @see ServiceProvider::isDeferred()
     * @see ServiceProvider::provides()
     * @see Container::getProvider()
     * @see ServiceProvider::boot()
     */
    public function register($serviceProviderClass, ...$alias)
    {
        /** @var ServiceProvider $provider */
        $provider = new $serviceProviderClass($this);
        if (!$provider->isDeferred()) {
            $provider->register();
        } else {
            $provided = $provider->provides();
            if (!is_array($provided) || count($provided) === 0) {
                throw new ContainerException(
                    "Service provider '{$serviceProviderClass}' is marked as deferred" .
                    " but is not providing any implementation."
                );
            }
            foreach ($provided as $id) {
                $this->bindings[$id] = $this->getDeferredProviderMakeClosure($provider, $id);
            }
        }

        try {
            $bootMethod = new ReflectionMethod($provider, 'boot');
        } catch (ReflectionException $e) {
            throw new ContainerException('Could not reflect on the provider boot method.');
        }

        $requiresBoot = ($bootMethod->getDeclaringClass()->getName() === get_class($provider));
        if ($requiresBoot) {
            $this->bootable[] = $provider;
        }
        $this->bindings[$serviceProviderClass] = $provider;
        $this->singletons[$serviceProviderClass] = true;
        foreach ($alias as $a) {
            $this->bindings[$a] = $provider;
            $this->singletons[$a] = true;
        }
    }

    /**
     * Returns a closure that will build a provider on demand, if an implementation provided by the provider is
     * required.
     *
     * @param ServiceProvider $provider The provider instance to register.
     * @param string          $id       The id of the implementation to bind.
     *
     * @return Closure A Closure ready to be bound to the id as implementation.
     */
    private function getDeferredProviderMakeClosure(ServiceProvider $provider, $id)
    {
        return function () use ($provider, $id) {
            static $registered;
            if ($registered === null) {
                $provider->register();
                $registered = true;
            }

            return $this->get($id);
        };
    }

    /**
     * Binds an interface a class or a string slug to an implementation and will always return the same instance.
     *
     * @param string             $id                A class or interface fully qualified name or a string slug.
     * @param mixed              $implementation    The implementation that should be bound to the alias(es); can be a
     *                                              class name, an object or a closure.
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return void This method does not return any value.
     * @throws ContainerException If there's any issue reflecting on the class, interface or the implementation.
     */
    public function singleton($id, $implementation = null, array $afterBuildMethods = null)
    {
        $this->singletons[$id] = true;
        $this->bindings[$id] = $this->buildClosure($id, $implementation, $afterBuildMethods);
    }

    /**
     * Binds an interface, a class or a string slug to an implementation.
     *
     * Existing implementations are replaced.
     *
     * @param string             $id                A class or interface fully qualified name or a string slug.
     * @param mixed              $implementation    The implementation that should be bound to the alias(es); can be a
     *                                              class name, an object or a closure.
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return void The method does not return any value.
     * @throws ContainerException      If there's an issue while trying to bind the implementation.
     *
     */
    public function bind($id, $implementation = null, array $afterBuildMethods = null)
    {
        unset($this->singletons[$id]);
        $this->bindings[$id] = $this->buildClosure($id, $implementation, $afterBuildMethods);
    }

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     *
     * If there are bootable providers (providers overloading the `boot` method) then the `boot` method will be
     * called on each bootable provider.
     *
     * @return void This method does not return any value.
     * @see ServiceProvider::boot()
     *
     */
    public function boot()
    {
        if (!empty($this->bootable)) {
            foreach ($this->bootable as $provider) {
                /** @var ServiceProvider $provider */
                $provider->boot();
            }
        }
    }

    /**
     * Binds a class, interface or string slug to a chain of implementations decorating a base
     * object; the chain will be lazily resolved only on the first call.
     *
     * The base decorated object must be the last element of the array.
     *
     * @param string                        $id                The class, interface or slug the decorator chain should
     *                                                         be bound to.
     * @param array<string|object|callable> $decorators        An array of implementations that decorate an object.
     * @param array<string>|null            $afterBuildMethods An array of methods that should be called on the
     *                                                         instance after it has been built; the methods should not
     *                                                         require any argument.
     *
     * @return void This method does not return any value.
     */
    public function singletonDecorators($id, $decorators, array $afterBuildMethods = null)
    {
        $this->bindings[$id] = $this->getDecoratorClosure($decorators, $id, true, $afterBuildMethods);
    }

    /**
     * Builds and returns a closure that will start building the chain of decorators.
     *
     * @param array<string|object|callable> $decorators        A list of decorators.
     * @param string                 $id                The id to bind the decorator tail to.
     * @param bool                   $singleton         Whether the create the closure for singletons or not.
     * @param array<string>|null     $afterBuildMethods A set of method to run on the built decorated instance after
     *                                                  it's built.
     * @return callable The callable or Closure that will start building the decorator chain.
     *
     * @throws ContainerException If there's any issue while trying to register any decorator step.
     */
    private function getDecoratorClosure(array $decorators, $id, $singleton, array $afterBuildMethods = null)
    {
        $decorator = array_pop($decorators);

        if ($decorator === null) {
            throw new ContainerException('The decorator chain cannot be empty.');
        }

        do {
            $previous = isset($buildClosure) ? $buildClosure : null;
            $buildClosure = $this->buildClosure($id, $decorator, $afterBuildMethods, $previous);
            $decorator = array_pop($decorators);
            $afterBuildMethods = [];
        } while ($decorator !== null);

        if (!is_callable($buildClosure)) {
            throw new ContainerException(
                "Decorator build closure for '{$id}' should be a function returning an object, not an object. " .
                'The `$id` parameter should be a string, not an object. '
            );
        }

        if ($singleton) {
            $this->singletons[$id] = true;
        }

        return $buildClosure;
    }

    /**
     * Binds a class, interface or string slug to to a chain of implementations decorating a
     * base object.
     *
     * The base decorated object must be the last element of the array.
     *
     * @param string                        $id                The class, interface or slug the decorator chain should
     *                                                         be bound to.
     * @param array<string|object|callable> $decorators        An array of implementations that decorate an object.
     * @param array<string>|null            $afterBuildMethods An array of methods that should be called on the
     *                                                         instance after it has been built; the methods should not
     *                                                         require any argument.
     *
     * @return void This method does not return any value.
     * @throws ContainerException If there's any issue binding the decorators.
     */
    public function bindDecorators($id, array $decorators, array $afterBuildMethods = null)
    {
        $this->bindings[$id] = $this->getDecoratorClosure($decorators, $id, false, $afterBuildMethods);
    }

    /**
     * Unsets a binding or tag in the container.
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void The method does not return any value.
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset], $this->tags[$offset], $this->singletons[$offset]);
    }

    /**
     * Starts the `when->needs->give` chain for a contextual binding.
     *
     * @param string $class The fully qualified name of the requesting class.
     *
     * Example:
     *
     *      // Any class requesting an implementation of `LoggerInterface` will receive this implementation ...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // But if the requesting class is `Worker` return another implementation
     *      $container->when('Worker')
     *          ->needs('LoggerInterface)
     *          ->give('RemoteLogger);
     *
     * @return Container The container instance, to continue the when/needs/give chain.
     */
    public function when($class)
    {
        $this->whenClass = $class;

        return $this;
    }

    /**
     * Second step of the `when->needs->give` chain for a contextual binding.
     *
     * Example:
     *
     *      // Any class requesting an implementation of `LoggerInterface` will receive this implementation ...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // But if the requesting class is `Worker` return another implementation.
     *      $container->when('Worker')
     *          ->needs('LoggerInterface)
     *          ->give('RemoteLogger);
     *
     * @param string $id The class or interface needed by the class.
     *
     * @return Container The container instance, to continue the when/needs/give chain.
     */
    public function needs($id)
    {
        $this->needsClass = $id;

        return $this;
    }

    /**
     * Third step of the `when->needs->give` chain for a contextual binding.
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
     * @param mixed $implementation The implementation specified
     *
     * @return void This method does not return any value.
     */
    public function give($implementation)
    {
        $this->whenNeedsGive[$this->whenClass][$this->needsClass] = $implementation;
        unset($this->whenClass, $this->needsClass);
    }

    /**
     * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
     * bound to `$id` and return the value of a call to `$method` method with the call arguments.
     *
     * @param string|object $id               A fully-qualified class name, a bound slug or an object o call the
     *                                        callback on.
     * @param string        $method           The method that should be called on the resolved implementation with the
     *                                        specified array arguments.
     *
     * @return mixed The called method return value.
     * @throws ContainerException
     */
    public function callback($id, $method)
    {
        $callbackIdPrefix = is_object($id) ? spl_object_hash($id) : $id;

        if (!is_string($callbackIdPrefix)) {
            throw new ContainerException(
                "Callbacks can only be built on ids, class names or objects; '{$id}' is neither."
            );
        }

        if (!is_string($method)) {
            throw new ContainerException("Callbacks second argument must be a string method name.");
        }

        $callbackId = $callbackIdPrefix . '::' . $method;

        if (isset($this->callbacks[$callbackId])) {
            return $this->callbacks[$callbackId];
        }

        $callbackClosure = function (...$args) use ($id, $method) {
            $instance = is_string($id) && isset($this->bindings[$id]) ? $this->get($id) : $this->resolve($id);
            return $instance->{$method}(...$args);
        };

        if ($this->isSingleton($id) || $this->isStaticMethod($id, $method)) {
            // If we can know immediately, without actually resolving the binding, then build and cache immediately.
            $this->callbacks[$callbackId] = $callbackClosure;
        }

        return $callbackClosure;
    }

    /**
     * Returns whether a string id maps to a resolved singleton or not.
     *
     * @param mixed $id The string, class name or object to check.
     *
     * @return bool Whether the id maps to something bound as singleton or not.
     */
    private function isSingleton($id)
    {
        return is_string($id) && isset($this->singletons[$id]);
    }

    /**
     * Whether a method of an id, possibly not a class, is static or not.
     *
     * @param object|string $object A class name, instance or something that does not map to a class.
     * @param string        $method The method to check.
     *
     * @return bool Whether a method of an id or class is static or not.
     */
    protected function isStaticMethod($object, $method)
    {
        $key = is_string($object) ? $object . '::' . $method : get_class($object) . '::' . $method;

        if (!isset(static::$isStaticMethodCache[$key])) {
            try {
                static::$isStaticMethodCache[$key] = (new ReflectionMethod($object, $method))->isStatic();
            } catch (ReflectionException $e) {
                return false;
            }
        }

        return static::$isStaticMethodCache[$key];
    }

    /**
     * Returns a callable object that will build an instance of the specified class using the
     * specified arguments when called.
     *
     * The callable will be a closure on PHP 5.3+ or a lambda function on PHP 5.2.
     *
     * @param string             $id                The fully qualified name of a class or an interface.
     * @param array<mixed>       $buildArgs         An array of arguments that should be used to build the instance;
     *                                              note that any argument will be resolved using the container itself
     *                                              and bindings will apply.
     * @param array<string>|null $afterBuildMethods An array of methods that should be called on the built
     *                                              implementation after resolving it.
     *
     * @return callable  A callable function that will return an instance of the specified class when
     *                   called.
     * @throws ContainerException If a closure to build the object cannot be created.
     */
    public function instance($id, array $buildArgs = [], array $afterBuildMethods = null)
    {
        if (empty($buildArgs)) {
            $buildClosure = $this->getInstanceClosure($id);
        } else {
            $buildClosure = $this->buildClosure($id, $id, $afterBuildMethods, ...$buildArgs);
            if (!is_callable($buildClosure)) {
                throw new ContainerException(
                    "Build closure for '{$id}' should be a function returning an object, not an object. " .
                    'The `$id` parameter should be a string, not an object. '
                );
            }
        }
        return $buildClosure;
    }

    /**
     * Builds and returns a closure to be used to lazily make objects on PHP 5.3+ and return them.
     *
     * @param string|object $id The id to produce the closure for.
     *
     * @return Closure The closure that will produce the instance.
     */
    protected function getInstanceClosure($id)
    {
        return function () use ($id) {
            return is_string($id) ? $this->get($id) : $this->resolve($id);
        };
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool Whether the container contains a binding for an id or not.
     */
    public function has($id)
    {
        return isset($this->bindings[$id]) || (is_string($id) && $this->classExists($id));
    }

    /**
     * Returns whether a binding exists in the container or not.
     *
     * `isBound($id)` returning `true` means the a call to `bind($id, $implementaion)` or `singleton($id,
     * $implementation)` (or equivalent ArrayAccess methods) was explicitly made.
     *
     * @param string $id The id to check for bindings in the container.
     *
     * @return bool Whether an explicit binding for the id exists in the container or not.
     */
    public function isBound($id)
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Protects a value to make sure it will not be resolved, if callable or if the name of an existing class.
     *
     * @param mixed $value The value to protect.
     *
     * @return ProtectedValue A protected value instance, its value set to the provided value.
     */
    public function protect($value)
    {
        return ProtectedValue::of($value);
    }

    /**
     * Returns the Service Provider instance registered.
     *
     * @param string $providerId The Service Provider clas to return the instance for.
     *
     * @return ServiceProvider The service provider instance.
     *
     * @throws NotFoundException If the Service Provider class was never registered in the container.
     */
    public function getProvider($providerId)
    {
        if (!isset($this->bindings[$providerId])) {
            throw new NotFoundException("Service provider '{$providerId}' is not registered in the container.");
        }

        return $this->get($providerId);
    }

    /**
     * Checks a class exists and is instantiatable.
     *
     * @param string $className The class name to check for.
     *
     * @return void This method does not return any value.
     * @throws ContainerException If the class cannot be instantiated.
     *
     */
    private function ensureClassIsInstantiatable($className)
    {
        try {
            // @phpstan-ignore-next-line
            $classReflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
        if (!$classReflection->isInstantiable()) {
            throw new ContainerException(
                sprintf(
                    'To bind a class in the Container without defining an implementation' .
                    ', the class must be instantiable. %s is not instantiable.',
                    $className
                )
            );
        }
        // Cache the reflection.
        $this->reflections[$className] = $classReflection;
    }
}
