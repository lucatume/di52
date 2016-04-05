<?php

class BaseClassDecoratorOne implements BaseClassInterface
{
    /**
     * @var BaseClassInterface
     */
    private $baseClass;

    public function __construct(BaseClassInterface $baseClass)
    {
        $this->baseClass = $baseClass;
    }

    public function doSomething()
    {
        return __CLASS__ . $this->baseClass->doSomething();
    }
}