<?php

class PrimitiveDependingClassTwo
{
    /**
     * @var int
     */
    public $number;

    public function __construct($number)
    {
        $this->number = $number;
    }
}