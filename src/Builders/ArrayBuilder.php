<?php
/**
 * A builder wrapping an array of builders that will return upon build.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52\Builders;

use lucatume\DI52\ContainerException;

/**
 * Class ArrayBuilder
 *
 * @package lucatume\DI52\Builders
 */
class ArrayBuilder implements BuilderInterface
{
    /**
     * The values the instance of the builder holds.
     *
     * @var list<BuilderInterface>
     */
    private $values = [];

    /**
     * ArrayBuilder constructor.
     *
     * @param BuilderInterface $value The value to add to the array of builders.
     */
    public function __construct(BuilderInterface $value)
    {
        $this->values[] = $value;
    }

    /**
     * @param BuilderInterface $value The value to add to the array of builders.
     *
     * @return void
     */
    public function add(BuilderInterface $value)
    {
        $this->values[] = $value;
    }

    /**
     * Builds and returns an instance of the builder built with the specified value.
     *
     * @param BuilderInterface  $base The base value the instance of the builder should be built for.
     * @param ?BuilderInterface $add  A builder that should be added to the instance of the builder.
     *
     * @return BuilderInterface An instance of the builder built on the specified value.
     */
    public static function of(BuilderInterface $base, ?BuilderInterface $add = null)
    {
        $builder = $base instanceof self ? $base : new self($base);
        if ($add) {
            $builder->add($add);
        }
        return $builder;
    }

    /**
     * Returns the value wrapped by the builder.
     *
     * @return mixed[] The value wrapped by the builder.
     *
     * @throws ContainerException When one of the builders does not resolve to array.
     */
    public function build()
    {
        $final = [];
        foreach ($this->values as $value) {
            $result = $value->build();
            if (!is_array($result)) {
                throw new ContainerException('An additive binding did not resolve to array!');
            }

            $final[] = $result;
        }

        return array_merge(...$final);
    }
}
