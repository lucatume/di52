<?php

class ClassOneWithCounter extends ClassOne
{
    protected static $count = 0;

    public function __construct()
    {
        self::$count += 1;

        $this->var = self::$count;
    }

}