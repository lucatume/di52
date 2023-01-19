<?php
/**
 * PHP7+ contextual binding test classes.
 */

class Concrete7Dependency {

}

class Primitive7ConstructorClass
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
     * @var string|null
     */
    protected $optional;

    public function __construct(int $num, Concrete7Dependency $dependency, string $hello, $optional = null)
    {
        $this->num = $num;
        $this->dependency = $dependency;
        $this->hello = $hello;
        $this->optional = $optional;
    }

    public function num(): int {
        return $this->num;
    }

    public function dependency(): Concrete7Dependency {
        return $this->dependency;
    }

    public function hello(): string {
        return $this->hello;
    }

    public function optional(): ?string {
        return $this->optional;
    }
}
