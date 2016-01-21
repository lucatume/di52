<?php

class DependingClassTwo
{
    /**
     * @var ExtendingClassOne
     */
    public $classOne;

    public function __construct(ConcreteClassImplementingTestInterfaceOne $classOne)
    {
        $this->classOne = $classOne;
    }
}