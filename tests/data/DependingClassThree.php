<?php

class DependingClassThree
{
    /**
     * @var ConcreteClassOne
     */
    public $classOne;

    public function __construct(ConcreteClassOne $classOne)
    {
        $this->classOne = $classOne;
    }
}