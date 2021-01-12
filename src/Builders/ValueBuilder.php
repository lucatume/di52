<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52
 */


namespace lucatume\DI52\Builders;

class ValueBuilder implements BuilderInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * ValueBuilder constructor.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function of($value)
    {
        return $value instanceof self ? $value : new self($value);
    }

    public function build()
    {
        return $this->value;
    }
}
