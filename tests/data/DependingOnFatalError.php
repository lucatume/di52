<?php
class DependingOnFatalError
{
    public function __construct(FatalErrorClassThree $three)
    {
    }
}

class Lorem
{
    public function __construct(Dolor $dolor)
    {
    }
}

class Dolor
{
    public function __construct(FatalErrorClassFour $four)
    {
    }
}
