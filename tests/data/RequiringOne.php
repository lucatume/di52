<?php

class RequiringOne
{
    /**
     * @var ClassOne
     */
    private $one;

    public function __construct(ClassOne $one)
    {
        $this->one = $one;
    }

    /**
     * @return ClassOne
     */
    public function getOne()
    {
        return $this->one;
    }

}