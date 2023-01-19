<?php
/**
 * PHP81+ contextual binding test classes.
 */

class Concrete81Dependency {

}

class Primitive81ConstructorClass
{

    public function __construct(
        private readonly int $num,
        private readonly Concrete81Dependency $dependency,
        protected readonly string $hello,
        private readonly ?string $optional = null
    ) {}

    public function num(): int {
        return $this->num;
    }

    public function dependency(): Concrete81Dependency {
        return $this->dependency;
    }

    public function hello(): string {
        return $this->hello;
    }

    public function optional(): ?string {
        return $this->optional;
    }
}
