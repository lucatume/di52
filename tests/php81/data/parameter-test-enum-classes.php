<?php

enum TestBackedEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}

class ClassWithEnumDependency
{
    public function __construct(
        private readonly TestBackedEnum $status
    ) {
    }
}

class UnionTypeEnumClass
{

    public function __construct(
        private readonly TestBackedEnum|string $status
    ) {
    }
}
