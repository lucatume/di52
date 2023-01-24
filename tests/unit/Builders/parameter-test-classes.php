<?php

class ParameterTestClassOne
{
    public function __construct($one, string $two, $three = 'three', string $four = 'four')
    {
    }
}

class ParameterTestClassTwo
{
    public function __construct($one, int $two, $three = 3, int $four = 4)
    {
    }
}

class ParameterTestClassThree
{
    public function __construct($one, bool $two, $three = true, bool $four = false)
    {
    }
}

class ParameterTestClassFour
{
    public function __construct($one, float $two, $three = 2.3, float $four = 8.9)
    {
    }
}

class ParameterTestClassFive
{
    public function __construct($one, array $two, $three = [], array $four = ['four','five'=>'six'])
    {
    }
}

class ParameterTestClassSix
{
    public function __construct($one, callable $two, $three = null, callable $four = null)
    {
    }
}

class ParameterTestClassSeven
{
    public function __construct($one, iterable $two, $three = null, iterable $four = null)
    {
    }
}

class ParameterTestClassEight
{
    public function __construct(ParameterTestClassOne $one, ParameterTestClassTwo $two = null)
    {
    }
}
