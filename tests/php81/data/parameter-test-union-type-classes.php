<?php

class UnionTypeClass
{
    protected int|float|string $status;

    public function __construct(int|float|string $status)
    {
        $this->status = $status;
    }
}

class UnionTypePromotedClass
{

    public function __construct(
        private int|float|string $status
    ) {
    }
}
