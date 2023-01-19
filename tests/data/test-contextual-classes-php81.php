<?php
/**
 * PHP81+ contextual binding test classes.
 */

final class Concrete81Dependency {

}

final class Primitive81ConstructorClass
{

    final public function __construct(
        private readonly int $num,
        private readonly Concrete81Dependency $dependency,
        protected readonly string $hello,
        private readonly ?string $optional = null
    ) {}

    final public function num(): int {
        return $this->num;
    }

    final public function dependency(): Concrete81Dependency {
        return $this->dependency;
    }

    final public function hello(): string {
        return $this->hello;
    }

    final public function optional(): ?string {
        return $this->optional;
    }
}
