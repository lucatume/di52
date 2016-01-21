<?php

class ObjectFive
{

    public $dependency;
    public $string;
    public $int;

    public static function makeOne(DependencyObjectOne $dependency)
    {
        $instance = new self();
        $instance->dependency = $dependency;

        return $instance;
    }

    public function setDependency(DependencyObjectOne $dependency)
    {
        $this->dependency = $dependency;
    }

    public function setString($string)
    {
        $this->string = $string;
    }

    public function setInt($int)
    {
        $this->int = $int;
    }
}
