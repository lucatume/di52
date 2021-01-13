<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Builders
 */

namespace lucatume\DI52\Builders;

class Resolver
{

    /**
     * @var array<string,BuilderInterface|ReinitializableBuilderInterface>
     */
    protected $bindings = [];
    protected $resolveUnboundAsSingletons = false;
    protected $singletons = [];
    protected $whenNeedsGive = [];
    /**
     * @var array
     */
    protected $buildLine = [];

    public function __construct($resolveUnboundAsSingletons = false)
    {
        $this->resolveUnboundAsSingletons = $resolveUnboundAsSingletons;
    }

    public function bind($id, BuilderInterface $implementation)
    {
        unset($this->singletons[$id]);
        $this->bindings[$id] = $implementation;
    }

    public function singleton($id, BuilderInterface $implementation)
    {
        $this->singletons[$id] = true;
        $this->bindings[$id] = $implementation;
    }

    public function isBound($id)
    {
        return isset($this->bindings[$id]);
    }

    public function unbind($offset)
    {
        unset($this->bindings[$offset]);
    }

    public function isSingleton($id)
    {
        return isset($this->singletons[$id]);
    }

    /**
     * @param string $id               The ID to resolve the when-needs-give case for.
     * @param string $paramClass       The class of the parameter to solve the when-needs-give case for.
     * @return BuilderInterface|string Either the builder for the when-needs-give replacement, or the input parameter
     *                                 class if not found.
     */
    public function whenNeedsGive($id, $paramClass)
    {
        return isset($this->whenNeedsGive[$id][$paramClass]) ?
            $this->whenNeedsGive[$id][$paramClass]
            : $paramClass;
    }

    public function setWhenNeedsGive($whenClass, $needsClass, BuilderInterface $builder)
    {
        $this->whenNeedsGive[$whenClass][$needsClass] = $builder;
    }

    /**
     * Resolves an ide to an implementation with the input arguments.
     *
     * @param            string|mixed $id The id, class name or built value to resolve.
     * @param array<string>|null $afterBuildMethods  A list of methods that should run on the built instance.
     * @param mixed      ...$buildArgs A set of build arguments that will be passed to the implementation constructor.
     * @return BuilderInterface|ReinitializableBuilderInterface|mixed The builder, set up to use the specified set of
     *                                                                build arguments.
     */
    public function resolveWithArgs($id, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (empty($afterBuildMethods) && empty($buildArgs)) {
            return $this->resolve($id);
        }
        return $this->cloneBuilder($id, $afterBuildMethods, ...$buildArgs)->build();
    }

    public function resolve($id, array $buildLine = null)
    {
        if ($buildLine !== null) {
            $this->buildLine = $buildLine;
        }

        if (!isset($this->bindings[$id])) {
            return $this->resolveUnbound($id);
        }

        if ($this->bindings[$id] instanceof BuilderInterface) {
            $built = $this->resolveBound($id);
        } else {
            $built = $this->bindings[$id];
        }

        return $built;
    }

    private function resolveUnbound($id)
    {
        $built = (new ClassBuilder($id, $this, $id))->build();
        if ($this->resolveUnboundAsSingletons) {
            $this->singletons[$id] = true;
            $this->bindings[$id] = $built;
        }
        return $built;
    }

    private function resolveBound($id)
    {
        $built = $this->bindings[$id]->build();
        if (isset($this->singletons[$id])) {
            $this->bindings[$id] = $built;
        }
        return $built;
    }

    private function cloneBuilder($id, array $afterBuildMethods = null, ...$buildArgs)
    {
        if (isset($this->bindings[$id])) {
            $builder = clone $this->bindings[$id];
            if ($builder instanceof ReinitializableBuilderInterface) {
                $builder->reinit($afterBuildMethods, ...$buildArgs);
            }
        } else {
            $builder = new ClassBuilder($id, $this, $id, $afterBuildMethods, ...$buildArgs);
        }

        return $builder;
    }

    public function addToBuildLine($type, $parameterName = null)
    {
        $this->buildLine[] = $parameterName ? trim("{$type} \${$parameterName}") : $type;
    }

    public function getBuildLine()
    {
        return $this->buildLine;
    }
}
