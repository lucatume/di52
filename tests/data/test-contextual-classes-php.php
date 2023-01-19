<?php
/**
 * PHP5.3+ contextual binding test classes.
 */

class Concrete53Dependency {

}

class Primitive53ConstructorClass
{
    /**
     * @var int
     */
    private $num;

    /**
     * @var Concrete53Dependency
     */
    private $dependency;

    /**
     * @var string
     */
    protected $hello;

    /**
     * @var null|string
     */
    protected $optional;

    public function __construct($num, Concrete53Dependency $dependency, $hello, $optional = null)
    {
        $this->num = $num;
        $this->dependency = $dependency;
        $this->hello = $hello;
        $this->optional = $optional;
    }

    public function num() {
        return $this->num;
    }

    public function dependency() {
        return $this->dependency;
    }

    public function hello() {
        return $this->hello;
    }

    public function optional() {
        return $this->optional;
    }

}
