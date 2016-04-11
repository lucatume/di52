<?php

class CustomClassThree
{
    /**
     * @var TestInterfaceOne
     */
    private $one;
    /**
     * @var TestInterfaceTwo
     */
    private $two;

    public function __construct(TestInterfaceOne $one, TestInterfaceTwo $two)
    {

        $this->one = $one;
        $this->two = $two;
    }

    /**
     * @return TestInterfaceOne
     */
    public function getOne()
    {
        return $this->one;
    }

    /**
     * @return TestInterfaceTwo
     */
    public function getTwo()
    {
        return $this->two;
    }
}