<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52
 */


namespace lucatume\DI52\Builders;

use Closure;
use lucatume\DI52\Container;
use lucatume\DI52\NotFoundException;

class Factory
{
    /**
     * @var Container
     */
    private $resolver;
    /**
     * @var Container
     */
    private $container;

    /**
     * BuilderFactory constructor.
     */
    public function __construct(Container $container, Resolver $resolver)
    {
        $this->container = $container;
        $this->resolver = $resolver;
    }

    /**
     * Returns the correct builder for a value.
     *
     * @param string             $id                 The id to build the builder for.
     * @param mixed              $implementation     The implementation to build the builder for.
     * @param array<string>|null $afterBuildMethods  A list of methods that should be called on the built instance
     *                                               after
     *                                               it's been built.
     * @param mixed              ...$buildArgs       A set of arguments to pass that should be used to build the
     *                                               instance, if any.
     * @return BuilderInterface A builder instance.
     * @throws NotFoundException
     */
    public function getBuilder($id, $implementation = null, array $afterBuildMethods = null, ...$buildArgs)
    {
        if ($implementation === null) {
            $implementation = $id;
        }
        if (is_string($implementation)) {
            if (class_exists($implementation)) {
                return new ClassBuilder($id, $this->resolver, $implementation, $afterBuildMethods, ...$buildArgs);
            }
            return new ValueBuilder($implementation);
        }

        if ($implementation instanceof BuilderInterface) {
            return $implementation;
        }

        if ($implementation instanceof Closure) {
            return new ClosureBuilder($this->container, $implementation);
        }

        if (is_callable($implementation)) {
            return new CallableBuilder($this->container, $implementation);
        }

        return new ValueBuilder($implementation);
    }
}
