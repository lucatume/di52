<?php

class tad_DI52_Bindings_Resolver implements tad_DI52_Bindings_ResolverInterface
{
    /**
     * @var tad_DI52_Bindings_ImplementationCallback[]
     */
    protected $bindings = array();

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
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function resolve($classOrInterface)
    {
        $isClass = class_exists($classOrInterface);
        $isInterface = interface_exists($classOrInterface);
        if (!($isInterface || $isClass)) {
            throw new InvalidArgumentException("[{$classOrInterface}] does not exist");
        }
        $isBound = array_key_exists($classOrInterface, $this->bindings);
        if (!$isBound) {
            return $this->resolveUnbound($classOrInterface);
        }
        return $this->resolveBound($classOrInterface);
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