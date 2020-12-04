<?php
/**
 * The Dependency Injection container.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52;

use Closure;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
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
 */
class Container implements \ArrayAccess, ContainerInterface
{
    /**
     * @var array<ServiceProvider>
     */
    protected $deferred = [];

    /**
     * @var array<string,ReflectionClass>
     */
    protected $reflections = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
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
     * @var array<string,callable>
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
     * Sets a variable on the container.
     *
     * @param string $key   The alias the container will use to reference the variable.
     * @param mixed  $value The variable value.
     *
     * @return void The method does not return any value.
     */
    public function setVar($key, $value)
    {
        $this->bindings[ $key ] = $this->getMakeClosure($key, ProtectedValue::of($value));
    }

    /**
     *
     *
     * @since TBD
     *
     * @param callable|string|array<string> $implementation    The implementation to build the Closure for.
     * @param array<string>                 $afterBuildMethods A set of methods that should be called on the instance
     *                                                         after it's built and before it's returned.
     * @param array<mixed>                  $buildArgs         An array of arguments to use when building the instance.
     *
     * @return Closure|callable|false Either a closure to build the implementation, or `false` if the implementation is
     *                       not buildable.
     */
    private function getMakeClosure($id, $implementation, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (is_callable($implementation)) {
            // Ready to run.
            return $implementation;
        }
        if (is_object($implementation)) {
            // Already built, just return this.
            return static function () use ($implementation) {
                return $implementation;
            };
        }
        if (! ( is_string($implementation) && class_exists($implementation) )) {
            return ProtectedValue::of($implementation);
        }

        return function () use ($id, $implementation, $buildArgs, $afterBuildMethods) {
            $buildArgs = $this->resolveBuildArgs($id, $implementation, ...$buildArgs);
            $instance  = new $implementation(...$buildArgs);
            if (! empty($afterBuildMethods)) {
                foreach ($afterBuildMethods as $method) {
                    $instance->{$method}();
                }
            }

            return $instance;
        };
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
     * @throws NotFoundException
     */
    private function resolveBuildArgs($id, $className, ...$readyArgs)
    {
        $constructor = $this->getClassReflection($className)->getConstructor();
        if ($constructor === null) {
            // No constructor arguments to resolve.
            return [];
        }

        if (! $constructor->isPublic()) {
            throw new ContainerException("The '{$id}' class constructor method is not public.");
        }

        $resolved = [];
        foreach ($constructor->getParameters() as $i => $parameter) {
            if (isset($readyArgs[ $i ])) {
                $resolved[] = $this->makeInternally($readyArgs[ $i ], true);
                continue;
            }
            $resolved[] = $this->resolveParameter($parameter, $id);
        }

        return $resolved;
    }

    /**
     * Returns a ReflectionClass instance, built or cached.
     *
     * @param string $className The fully-qualifed class name to return the class reflection for.
     *
     * @return ReflectionClass The built class reflection object.
     * @throws ContainerException If there's an issue reflecting on the class.
     */
    private function getClassReflection($className)
    {
        try {
            return isset($this->reflections[ $className ]) ?
                $this->reflections[ $className ]
                : new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
    }

    /**
     * Makes something with the option of not checking for consistency and making it safely.
     *
     * @param string|mixed $id     A fully qualified class or interface name or an already built object.
     * @param bool         $safely Whether to throw when a string id does not map to a binding or an existing class or
     *                             not.
     *
     * @return mixed The value built by the container.
     * @throws NotFoundException If the target of the make is a string and is not bound to a valid, concrete, class
     *                           name.
     */
    private function makeInternally($id, $safely = false)
    {
        $this->makeLine[] = is_string($id) ? "'{$id}'" : 'object of type ' . gettype($id);
        $isString         = is_string($id);

        if ($isString && isset($this->deferred[ $id ])) {
            $this->deferred[ $id ]->register($this);
        }

        $isBound = $isString && isset($this->bindings[ $id ]);

        if (! $safely && ! $isBound && $isString && ! class_exists($id)) {
            throw new NotFoundException("Nothing is bound to the '{$id}' id and it's not an existing class.");
        }

        $maker = $isBound ? $this->bindings[ $id ] : $this->getMakeClosure($id, $id);

        return $maker($this);
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
        try {
            return $parameter->getDefaultValue();
        } catch (ReflectionException $e) {
            // The parameter is not optional, continue.
        }

        try {
            if (( $parameterClass = $this->getParameterClass($parameter) ) !== null) {
                $parameterClassName = $parameterClass->getName();
                if (isset($this->whenNeedsGive[ $id ][ $parameterClassName ])) {
                    $parameterClassName = $this->whenNeedsGive[ $id ][ $parameterClassName ];
                }
                $resolved = $this->makeInternally($parameterClassName);
                if (! is_object($resolved)) {
                    throw new ContainerException(
                        "Parameter '{$parameter->getName()}' could not be " .
                        "resolved to an object of class '{$parameterClassName}':" .
                        " bind '{$parameterClassName}' explicitly."
                    );
                }

                return $resolved;
            }
        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }

        throw new ContainerException("The {$parameter->getName()} is not a valid class " .
                                     "or does not have a default value; bind a closure to correctly build the object.");
    }

    /**
     * Returns the class of a parameter.
     *
     * @param ReflectionParameter $parameter The parameter to get the class for.
     *
     * @return ReflectionClass|null Either the parameter class or `null` if the parameter does not have a class.
     * @throws ReflectionException If there's an error reflecting on the parameter.
     */
    private function getParameterClass(ReflectionParameter $parameter)
    {
        if (PHP_VERSION_ID >= 80000) {
            $type = $parameter->getType();

            if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                /** @var class-string $className */
                $className = $type->getName();

                return new ReflectionClass($className);
            }

            return null;
        }

        return $parameter->getClass();
    }

    /**
     * Sets a variable on the container using the ArrayAccess API.
     *
     * When using the container as an array bindings will be bound as singletons; the two functions below are
     * equivalent:
     *
     *        $container->singleton('foo','ClassOne');
     *        $container['foo'] = 'ClassOne';
     *
     * Variables will be evaluated before storing, to protect a variable from the process, e.g. storing a closure, use
     * the `protect` method:
     *
     *        $container['foo'] = $container->protect($f));
     *
     * @param string $offset The alias the container will use to reference the variable.
     * @param mixed  $value  The variable value.
     *
     * @return void This method does not return any value.
     *
     * @see   Container::singleton()
     * @see   Container::protect()
     */
    public function offsetSet($offset, $value)
    {
        $this->bindings[ $offset ] = $this->getCachingClosure($this->getMakeClosure($offset, $value));
    }

    /**
     * Returns a Closure that will cache the callable results.
     *
     * @param callable $callable The callable that should be called to produce the result.
     *
     * @return Closure A Closure that will cache the callable results.
     */
    private function getCachingClosure(callable $callable)
    {
        return function () use ($callable) {
            static $result;
            if ($result === null) {
                $result = $callable($this);
            }

            return $result;
        };
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
     * @see lucatume\DI52\Container::make
     *
     */
    public function getVar($key, $default = null)
    {
        if (isset($this->bindings[ $key ])) {
            return $this->bindings[$key]($this);
        }

        return $default;
    }

    /**
     * Retrieves a variable or a binding from the database.
     *
     * If the offset is bound to an implementation then it will be resolved before returning it.
     *
     * * @param string|object $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (! $this->offsetExists($offset)) {
            throw new ContainerException("Nothing is bound to the key '{$offset}'");
        }

        return $this->make($offset);
    }

    /**
     * Whether a offset exists
     *
     * @since 5.0.0
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @see   isBound
     *
     */
    public function offsetExists($offset)
    {
        return isset($this->bindings[ $offset ]);
    }

    /**
     * Returns an instance of the class or object bound to an interface, class  or string slug if any, else it will try
     * to automagically resolve the object to a usable instance.
     *
     * If the implementation has been bound as singleton using the `singleton` method
     * or the ArrayAccess API then the implementation will be resolved just on the first request.
     *
     * @param string|object $id A fully qualified class or interface name or an already built object.
     *
     * @return mixed
     * @throws NotFoundException|ContainerException If the target of the make is not bound and is not a valid,
     *                                              concrete, class name or there's any issue making the target.
     */
    public function make($id)
    {
        $this->makeLine = [];
        try {
            return $this->makeInternally($id, false);
        } catch (ContainerException $e) {
            $exceptionClass = get_class($e);
            throw new $exceptionClass($this->buildMakeErrorMessage($id, $e));
        }
    }

    /**
     * Formats an error message to provide a useful debug message.
     *
     * @param string     $id The id of what is actually being built.
     * @param \Exception $e  The original exception thrown while trying to make the target.
     *
     * @return string The formatted make error message.
     */
    protected function buildMakeErrorMessage($id, \Exception $e)
    {
        $last      = array_pop($this->makeLine) ?: $id;
        $lastEntry = "Error while making {$last}: " . lcfirst(rtrim(
            str_replace('"', '', $e->getMessage()),
            '.'
        )) . '.';
        $frags     = array_merge($this->makeLine, [ $lastEntry ]);

        return implode("\n\t=> ", $frags);
    }

    /**
     * Tags an array of implementations bindings for later retrieval.
     *
     * The implementations can also reference interfaces, classes or string slugs.
     * Example:
     *
     *        $container->tag(['Posts', 'Users', 'Comments'], 'endpoints');
     *
     * @param array  $implementationsArray
     * @param string $tag
     *
     * @see lucatume\DI52\Container::tagged()
     *
     */
    public function tag(array $implementationsArray, $tag)
    {
        $this->tags[ $tag ] = $implementationsArray;
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
     * @see Container::tag()
     *
     */
    public function tagged($tag)
    {
        if (! $this->hasTag($tag)) {
            throw new NotFoundException("Nothing is tagged as '{$tag}'");
        }

        return array_map([ $this, 'make' ], $this->tags[ $tag ]);
    }

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     *
     * @return bool
     * @see lucatume\DI52\Container::tag()
     *
     */
    public function hasTag($tag)
    {
        return isset($this->tags[ $tag ]);
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
     *
     * @throws ContainerException If the Service Provider is not correctly configured or there's an issue
     *                            reflecting on it.
     *
     * @see ServiceProvider::boot()
     * @see ServiceProvider::register()
     * @see ServiceProvider::isDeferred()
     * @see ServiceProvider::provides()
     */
    public function register($serviceProviderClass)
    {
        /** @var ServiceProvider $provider */
        $provider = new $serviceProviderClass($this);
        if (! $provider->isDeferred()) {
            $provider->register();
        } else {
            $provided = $provider->provides();

            $count = count($provided);
            if ($count === 0) {
                throw new ContainerException(
                    "Service provider '{$serviceProviderClass}' is marked as deferred" .
                    " but is not providing any implementation."
                );
            }

            $this->bindings = array_merge($this->bindings, array_combine($provided, $provided));
            $this->deferred = array_merge(
                $this->deferred,
                array_combine($provided, array_fill(0, $count, $provider))
            );
        }
        try {
            $ref = new ReflectionMethod($provider, 'boot');
        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
        $requiresBoot = ( $ref->getDeclaringClass()->getName() === get_class($provider) );
        if ($requiresBoot) {
            $this->bootable[] = $provider;
        }
    }

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     *
     * If there are bootable providers (providers overloading the `boot` method) then the `boot` method will be
     * called on each bootable provider.
     *
     * @see ServiceProvider::boot()
     */
    public function boot()
    {
        if (! empty($this->bootable)) {
            foreach ($this->bootable as $provider) {
                /** @var ServiceProvider $provider */
                $provider->boot();
            }
        }
    }

    /**
     * Checks whether an interface, class or string slug has been bound in the container.
     *
     * @param string $id
     *
     * @return bool
     */
    public function isBound($id)
    {
        return $this->offsetExists($id);
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
     * @param array<string>                 $afterBuildMethods An array of methods that should be called on the
     *                                                         instance after it has been built; the methods should not
     *                                                         require any argument.
     *
     * @return void This method does not return any value.
     */
    public function singletonDecorators($id, $decorators, array $afterBuildMethods = null)
    {
        $this->bindDecorators($id, $decorators, $afterBuildMethods);
        $this->bindings[ $id ] = $this->getCachingClosure($this->bindings[ $id ]);
    }

    /**
     * Binds a class, interface or string slug to to a chain of implementations decorating a
     * base object.
     *
     * The base decorated object must be the last element of the array.
     *
     * @param string $id                The class, interface or slug the decorator chain should be bound to.
     * @param array  $decorators        An array of implementations that decorate an object.
     * @param array  $afterBuildMethods An array of methods that should be called on the instance after it has been
     *                                  built; the methods should not require any argument.
     *
     * @return void This method does not return any value.
     */
    public function bindDecorators($id, array $decorators, array $afterBuildMethods = null)
    {
        $decorator = array_pop($decorators);
        do {
            $previous          = isset($maker) ? $maker : null;
            $maker             = $this->getMakeClosure($id, $decorator, (array) $afterBuildMethods, $previous);
            $decorator         = array_pop($decorators);
            $afterBuildMethods = [];
        } while ($decorator !== null);
        $this->bindings[ $id ] = $maker;
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[ $offset ], $this->tags[ $offset ]);
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
     * @return lucatume\DI52\Container
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
     *      // any class requesting an implementation of `LoggerInterface` will receive this implementation...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // but if the requesting class is `Worker` return another implementation
     *      $container->when('Worker')
     *          ->needs('LoggerInterface)
     *          ->give('RemoteLogger);
     *
     * @param string $id The class or interface needed by the class.
     *
     * @return lucatume\DI52\Container
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
     */
    public function give($implementation)
    {
        $this->whenNeedsGive[ $this->whenClass ][ $this->needsClass ] = $implementation;
        unset($this->whenClass, $this->needsClass);
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
        return ProtectedValue::of($value);
    }

    /**
     * Binds an interface a class or a string slug to an implementation and will always return the same instance.
     *
     * @param string $id                A class or interface fully qualified name or a string slug.
     * @param mixed  $implementation    The implementation that should be bound to the alias(es); can be a class name,
     *                                  an object or a closure.
     * @param array  $afterBuildMethods An array of methods that should be called on the built implementation after
     *                                  resolving it.
     *
     * @return void This method does not return any value.
     * @throws ContainerException If there's any issue reflecting on the class, interface or the implementation.
     */
    public function singleton($id, $implementation = null, array $afterBuildMethods = null)
    {
        $this->bind($id, $implementation, $afterBuildMethods);
        $this->bindings[ $id ] = $this->getCachingClosure($this->bindings[ $id ]);
    }

    /**
     * Binds an interface, a class or a string slug to an implementation.
     *
     * Existing implementations are replaced.
     *
     * @param string $id                A class or interface fully qualified name or a string slug.
     * @param mixed  $implementation    The implementation that should be bound to the alias(es); can be a class name,
     *                                  an object or a closure.
     * @param array  $afterBuildMethods An array of methods that should be called on the built implementation after
     *                                  resolving it.
     *
     * @return void The method does not return any value.
     * @throws ContainerException      If there's an issue while trying to bind the implementation.
     *
     */
    public function bind($id, $implementation = null, array $afterBuildMethods = null)
    {
        if ($implementation === null) {
            // A call like `$container->bind(SomeClass::class);`
            $this->ensureClassIsInstantiatable($id);
            $implementation = $id;
        }

        $this->bindings[ $id ] = $this->getMakeClosure($id, $implementation, $afterBuildMethods);
    }

    /**
     * Checks a class exists and is instantiatable.
     *
     * @param string $className The class name to check for.
     *
     * @throws ContainerException If the class cannot be instantiated.
     */
    private function ensureClassIsInstantiatable($className)
    {
        try {
            $classReflection = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            throw new ContainerException($e->getMessage());
        }
        if (! $classReflection->isInstantiable()) {
            throw new ContainerException(sprintf(
                'To bind a class in the Container without defining an implementation' .
                ', the class must be instantiable. %s is not instantiable.',
                $className
            ));
        }
        // Cache the reflection.
        $this->reflections[ $className ] = $classReflection;
    }

    /**
     * Returns a lambda function suitable to use as a callback; when called the function will build the implementation
     * bound to `$id` and return the value of a call to `$method` method with the call arguments.
     *
     * @param string|object $id               A class or interface fully qualified name or a string slug.
     * @param string        $method           The method that should be called on the resolved implementation with the
     *                                        specified array arguments.
     *
     * @return mixed The called method return value.
     */
    public function callback($id, $method)
    {
        if (! is_string($method)) {
            throw new ContainerException('Callback method must be a string');
        }

        $callbackId = is_string($id) ? $id . '::' . $method : false;

        if ($callbackId && ! isset($this->callbacks[ $callbackId ]) && $this->isStaticMethod($id, $method)) {
            // If we can know immediately, without actually resolving the binding, then build and cache immediately.
            $this->callbacks[ $callbackId ] = $this->getCallbackClosure($id, $method);

            return $this->callbacks[ $callbackId ];
        }

        return $this->getCallbackClosure($id, $method);
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
        try {
            return ( new ReflectionMethod($object, $method) )->isStatic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

    /**
     * Returns a closure to be used as a callback function.
     *
     * @param string $id     The class name or identifier of class name or binding to call the method on.
     * @param string $method The name of the method.
     *
     * @return Closure The built callback closure.
     */
    protected function getCallbackClosure($id, $method)
    {
        $callbackId = is_string($id) ? $id . '::' . $method : false;

        if ($callbackId && isset($this->callbacks[ $callbackId ])) {
            return $this->callbacks[ $callbackId ];
        }

        $closure = function (...$args) use ($callbackId, $id, $method, &$closure) {
            $instance = $this->make($id);
            if ($callbackId && ! isset($this->callbacks[ $callbackId ]) && $this->isStaticMethod(
                $instance,
                $method
            )) {
                // If this is a callback for a static method, then cache it the first time we build it.
                $this->callbacks[ $callbackId ] = $closure;
            }

            return $instance->{$method}(...$args);
        };

        return $closure;
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
     */
    public function instance($id, array $buildArgs = [], array $afterBuildMethods = null)
    {
        return empty($buildArgs) ?
            $this->getInstanceClosure($id)
            : $this->getMakeClosure($id, $id, null, ...$buildArgs);
    }

    /**
     * Builds and returns a closure to be used to lazily make objects on PHP 5.3+ and return them.
     *
     * @param string $id The id to produce the closure for.
     *
     * @return Closure The closure that will produce the instance.
     */
    protected function getInstanceClosure($id)
    {
        return function () use ($id) {
            return $this->make($id);
        };
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed The entry for an id.
     *
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get($id)
    {
        // TODO implement get() method.
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
        // TODO implement has() method.
    }
}
