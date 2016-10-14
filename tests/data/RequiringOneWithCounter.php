<?php

class RequiringOneWithCounter extends RequiringOne
{
    public function __construct(ClassOneWithCounter $one)
    {
        parent::__construct($one);
    }

}