<?php
/**
 * PHP81+ contextual binding test classes.
 */

final class Concrete81Dependency
{

}

final class Primitive81ConstructorClass
{

    final public function __construct(
        private readonly int $num,
        private readonly Concrete81Dependency $dependency,
        protected readonly string $hello,
        protected readonly array $list,
        private readonly ?string $optional = null
    ) {
    }

    final public function num(): int
    {
        return $this->num;
    }

    final public function dependency(): Concrete81Dependency
    {
        return $this->dependency;
    }

    final public function hello(): string
    {
        return $this->hello;
    }

    final public function list(): array
    {
        return $this->list;
    }

    final public function optional(): ?string
    {
        return $this->optional;
    }
}

enum Status
{
    case DEFAULT;
    case PUBLISHED;
    case ARCHIVED;
}

enum StatusBacked: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}

final class EnumAsADependencyClass
{
    public function __construct(
        private readonly Status $status
    ) {
    }

    public function status(): Status
    {
        return $this->status;
    }
}

final class EnumAsADependencyWithDefaultValueClass
{
    public function __construct(
        private readonly Status $status = Status::DEFAULT
    ) {
    }

    public function status(): Status
    {
        return $this->status;
    }
}

final class BackedEnumClass
{
    public function __construct(
        private readonly StatusBacked $status
    ) {
    }

    public function status(): StatusBacked
    {
        return $this->status;
    }
}

final class BackedEnumWithDefaultValueClass
{
    public function __construct(
        private readonly StatusBacked $status = StatusBacked::DRAFT
    ) {
    }

    public function status(): StatusBacked
    {
        return $this->status;
    }
}

final class BackedEnumUnionClass
{
    public function __construct(
        private readonly StatusBacked|string $status
    ) {
    }

    public function status(): string
    {
        return is_string($this->status) ? $this->status : $this->status->value;
    }
}

final class BackedEnumUnionWithDefaultValueClass
{
    public function __construct(
        private readonly StatusBacked|string $status = StatusBacked::DRAFT
    ) {
    }

    public function status(): string
    {
        return is_string($this->status) ? $this->status : $this->status->value;
    }
}

final class DoubleEnumClass
{
    public function __construct(
        private readonly StatusBacked $statusBacked,
        private readonly Status $status
    ) {
    }

    public function statusBacked(): StatusBacked
    {
        return $this->statusBacked;
    }

    public function status(): Status
    {
        return $this->status;
    }
}
