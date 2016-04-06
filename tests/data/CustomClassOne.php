<?php

class CustomClassOne
{
    /**
     * @var TestInterfaceOne
     */
    private $one;

    /**
     * @return TestInterfaceOne
     */
    public function getOne()
    {
        return $this->one;
    }

    public function __construct(TestInterfaceOne $one)
    {

        $this->one = $one;
    }
}