<?php

interface One
{

}

interface Two
{

}

interface Four
{

}

interface Five
{

}

class ClassOne implements One
{

}

class ExtendingClassOneOne extends ClassOne
{

}

class ExtendingClassOneTwo extends ClassOne
{

}

class ClassOneOne implements One
{
    public function __construct()
    {

    }
}

class ClassOneTwo implements One
{
    public function __construct($foo = 'bar')
    {

    }
}

class ClassOneThree
{
    public function methodOne()
    {

    }

    public function methodTwo()
    {

    }
}

class ClassTwo implements Two
{
    public function __construct(One $one)
    {

    }
}

class ClassTwoOne implements Two
{
    public function __construct(ClassOne $one)
    {

    }
}

class ClassThree
{
    public function __construct(One $one, Two $two, $three = 3)
    {

    }
}

class ClassThreeOne
{
    public function __construct(One $one, ClassTwo $two, $three = 3)
    {

    }
}

class ClassThreeTwo
{
    public function __construct(ClassOne $one, ClassOneOne $two, $three = 3)
    {

    }
}

class ClassFour
{
    public function __construct($some)
    {

    }
}

class FourBase implements Four
{
    public function __construct()
    {

    }
}

class FourTwo implements Four
{

}

class FourDecoratorOne implements Four
{
    public function __construct(Four $decorated)
    {

    }
}

class FourDecoratorTwo implements Four
{
    public function __construct(Four $decorated)
    {

    }
}

class FourDecoratorThree implements Four
{
    public function __construct(Four $decorated)
    {

    }
}

class FiveBase implements Five
{
    public function __construct($foo = 10)
    {
    }
}

class FiveDecoratorOne implements Five
{
    public function __construct(Five $five, Four $four)
    {

    }
}

class FiveDecoratorTwo implements Five
{
    public function __construct(Five $five, One $one)
    {

    }
}

class FiveDecoratorThree implements Five
{
    public function __construct(Five $five, Two $two)
    {

    }
}

class FiveTwo implements Five
{

}


class ClassSix
{
    private $one;

    public function __construct(One $one)
    {
        $this->one = $one;
    }

    public function getOne()
    {
        return $this->one;
    }
}

class ClassSeven
{
    private $one;

    public function __construct(One $one)
    {

        $this->one = $one;
    }

    public function getOne()
    {
        return $this->one;
    }
}

class ClassSixOne
{
    private $one;

    public function __construct(ClassOne $one)
    {
        $this->one = $one;
    }

    public function getOne()
    {
        return $this->one;
    }
}

class ClassSevenOne
{
    private $one;

    public function __construct(ClassOne $one)
    {

        $this->one = $one;
    }

    public function getOne()
    {
        return $this->one;
    }
}
