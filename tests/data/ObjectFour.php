<?php

class ObjectFour
{

    public $myObject;
    public $string;

    public static function create(ObjectOne $myObject, $string)
    {
        $i = new self;
        $i->myObject = $myObject;
        $i->string = $string;

        return $i;
    }
}
