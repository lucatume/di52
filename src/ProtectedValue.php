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
     * ProtectedValue constructor.
     *
     * @param mixed $value The value to protect.
     */
    private function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Builds a protected value from a starting value.
     *
     * @param mixed|self $value Either a value to protect, or an already protected value.
     *
     * @return ProtectedValue The built protected value.
     */
    public static function of($value)
    {
        return $value instanceof self ? $value : new self($value);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Allows a protected value to be invoked as function to get its value.
     *
     * @return mixed The protected value.
     */
    public function __invoke()
    {
        return $this->getValue();
    }
}
