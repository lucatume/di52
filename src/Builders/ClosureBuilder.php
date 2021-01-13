<?php
/**
 * Closure-based builder.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52\Builders;

use Closure;
use lucatume\DI52\Container;

class ClosureBuilder implements BuilderInterface
{
    /**
     * @var Resolver
     */
    private $container;
    /**
     * @var Closure
     */
    private $closure;

    /**
     * ClosureBuilder constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container, Closure $closure)
    {
        $this->container = $container;
        $this->closure = $closure;
    }

    public function build()
    {
        $closure = $this->closure;
        return $closure($this->container);
    }
}
