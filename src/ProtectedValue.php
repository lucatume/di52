<?php
/**
 * An immutable value in the container.
 *
 * @package lucatume\DI52
 */

namespace lucatume\DI52;

/**
 * Class ProtectedValue
 *
 * @package lucatume\DI52
 */
class ProtectedValue
{

    /**
     * @var mixed
     */
    protected $value;

    /**
     * lucatume\DI52\ProtectedValue constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
