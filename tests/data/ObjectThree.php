<?php

class ObjectThree
{

    public $string;
    public $int;

    public static function one($string, $int)
    {
        $i = new self;
        $i->string = $string;
        $i->int = $int;

        return $i;
    }
}
