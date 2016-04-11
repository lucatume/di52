<?php

class CustomClassTwo
{
    /**
     * @var ClassOne
     */
    private $one;

    /**
     * @return ClassOne
     */
    public function getOne()
    {
        return $this->one;
    }

    public function __construct(ClassOne $one)
    {

        $this->one = $one;
    }
}