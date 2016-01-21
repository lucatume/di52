<?php

class DependingClassOne
{
    /**
     * @var TestInterfaceOne
     */
    public $testInterface;

    public function __construct(TestInterfaceOne $testInterface)
    {

        $this->testInterface = $testInterface;
    }
}