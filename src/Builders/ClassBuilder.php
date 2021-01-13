<?php
/**
 * Builds and returns object instances.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52\Builders;

use lucatume\DI52\ContainerException;
use lucatume\DI52\NotFoundException;
use ReflectionMethod;

/**
 * Class ClassBuilder
 *
 * @package lucatume\DI52\Builders
 */
class ClassBuilder implements BuilderInterface, ReinitializableBuilderInterface
{
    /**
     * An array cache of resolved constructor parameters, shared across all instances of the builder.
     * @var array<string,array<Parameter>>
     */
    protected static $constructorParametersCache = [];
    /**
     * A set of arguments that will be passed to the class constructor.
     *
     * @var array<mixed>
     */
    protected $buildArgs;
    /**
     * The id associated with the builder by the resolver.
     * @var string
     */
    protected $id;
    /**
     * The fully-qualified class name the builder should build instances of.
     *
     * @var string
     */
    protected $className;
    /**
     * A set of methods to call on the built object.
     *
     * @var array<string>|null
     */
    protected $afterBuildMethods;

    /**
     * A reference to the resolver currently using the builder.
     *
     * @var Resolver
     */
    protected $resolver;

    /**
     * ClassBuilder constructor.
     *
     * @param            string $id The identifier associated with this builder.
     * @param Resolver   $resolver A reference to the resolver currently using the builder.
     * @param string     $className The fully-qualified class name to build instances for.
     * @param array<string>|null $afterBuildMethods An optional set of methods to call on the built object.
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

    /**
     * Reinitialize the builder setting the after build methods and build args.
     *
     * @param array<string>|null $afterBuildMethods A set of methods to call on the object after it's built.
     * @param mixed              ...$buildArgs      A set of build arguments that will be passed to the constructor.
     *
     * @return void This method does not return any value.
     */
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
