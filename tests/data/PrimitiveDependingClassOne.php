<?php

class PrimitiveDependingClassOne
{
    /**
     * @var int
     */
    public $number;

    public function __construct($number = 23)
    {
        $this->number = $number;
    }
}