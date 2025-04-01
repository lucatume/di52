<?php

namespace unit\data;

class MySingletonClassTwo
{
    private $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }
}
