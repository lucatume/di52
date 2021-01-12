<?php

namespace Parameter\Test;

class ClassOne
{
}

class ClassTwo
{
}

class ClassThree
{
    public function __construct(ClassOne $one, \Parameter\Test\ClassTwo $two = null)
    {
    }
}
