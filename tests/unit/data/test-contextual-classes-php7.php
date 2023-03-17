<?php
/**
 * PHP7+ contextual binding test classes.
 */

interface Test7Interface
{

}

class Concrete7Dependency
{

}

class Primitive7ConstructorClass implements Test7Interface
{

    /**
     * @var int
     */
    private $num;

    /**
     * @var Concrete7Dependency
     */
    private $dependency;

    /**
     * @var string
     */
    protected $hello;

    /**
     * @var string[]
     */
    protected $list;

    /**
     * @var string|null
     */
    protected $optional;

    public function __construct(int $num, Concrete7Dependency $dependency, string $hello, array $list, $optional = null)
    {
        $this->num = $num;
        $this->dependency = $dependency;
        $this->hello = $hello;
        $this->list = $list;
        $this->optional = $optional;
    }

    public function num(): int
    {
        return $this->num;
    }

    public function dependency(): Concrete7Dependency
    {
        return $this->dependency;
    }

    public function hello(): string
    {
        return $this->hello;
    }

    public function list(): array
    {
        return $this->list;
    }

    /**
     * @return mixed|string|null
     */
    public function optional()
    {
        return $this->optional;
    }
}
