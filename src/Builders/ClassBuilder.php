<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52
 */


namespace lucatume\DI52\Builders;

use lucatume\DI52\ContainerException;
use lucatume\DI52\NotFoundException;
use ReflectionMethod;

class ClassBuilder implements BuilderInterface, ReinitializableBuilderInterface
{
    /**
     * @var array<string,array<Parameter>>
     */
    private static $constructorParametersCache = [];
    /**
     * @var array
     */
    protected $buildArgs;
    protected $id;
    /**
     * @var string
     */
    private $className;
    /**
     * @var array|null
     */
    private $afterBuildMethods;
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * ClassBuilder constructor.
     * @param            $id
     * @param Resolver   $resolver
     * @param string     $className
     * @param array|null $afterBuildMethods
     * @param mixed      ...$buildArgs
     * @throws NotFoundException
     */
    public function __construct($id, Resolver $resolver, $className, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (!class_exists($className)) {
            throw new NotFoundException(
                "nothing is bound to the '{$className}' id and it's not an existing or instantiable class."
            );
        }
        $this->id = $id;
        $this->className = $className;
        $this->afterBuildMethods = $afterBuildMethods;
        $this->resolver = $resolver;
        $this->buildArgs = $buildArgs;
    }

    public function build()
    {
        $constructorArgs = $this->resolveConstructorParameters();
        $built = new $this->className(...$constructorArgs);
        foreach ((array)$this->afterBuildMethods as $afterBuildMethod) {
            $built->{$afterBuildMethod}();
        }
        return $built;
    }

    private function resolveConstructorParameters()
    {
        $constructorArgs = [];

        /** @var Parameter $parameter */
        foreach ($this->getResolvedConstructorParameters($this->className) as $i => $parameter) {
            $this->resolver->addToBuildLine((string)$parameter->getType(), $parameter->getName());
            if (isset($this->buildArgs[$i])) {
                $arg = $this->buildArgs[$i] ;
                if ($arg instanceof BuilderInterface) {
                    $constructorArgs[] = $arg->build();
                    continue;
                }

                $constructorArgs[] = $this->resolveBuildArg($this->buildArgs[$i]);
                continue;
            }

            $constructorArgs [] = $this->resolveParameter($parameter);
        }

        return $constructorArgs;
    }

    private function getResolvedConstructorParameters($className)
    {
        if (isset(self::$constructorParametersCache[$className])) {
            return self::$constructorParametersCache[$className];
        }

        try {
            $constructorReflection = new ReflectionMethod($className, '__construct');
        } catch (\ReflectionException $e) {
            static::$constructorParametersCache[$className] = [];
            // No constructor method, no args.
            return [];
        }

        if (!$constructorReflection->isPublic()) {
            throw new ContainerException("constructor method is not public.");
        }

        $parameters = [];

        foreach ($constructorReflection->getParameters() as $i => $reflectionParameter) {
            $parameters[] = new Parameter($i, $reflectionParameter);
        }

        self::$constructorParametersCache[$className] = $parameters;

        return $parameters;
    }

    private function resolveParameter(Parameter $parameter)
    {
        $paramClass = $parameter->getClass();

        if ($paramClass) {
            $parameterImplementation = $this->resolver->whenNeedsGive($this->id, $paramClass);
            $resolved = $parameterImplementation instanceof BuilderInterface ?
                $parameterImplementation->build()
                : $this->resolver->resolve($parameterImplementation);
        } else {
            $resolved = $parameter->getDefaultValueOrFail();
        }
        return $resolved;
    }

    public function reinit(array $afterBuildMethods = null, ...$buildArgs)
    {
        $this->afterBuildMethods = $afterBuildMethods;
        $this->buildArgs = $buildArgs;
    }

    private function resolveBuildArg($arg)
    {
        if (is_string($arg) && ($this->resolver->isBound($arg) || class_exists($arg))) {
            return $this->resolver->resolve($arg);
        }
        return $arg;
    }
}
