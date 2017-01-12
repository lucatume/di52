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

class ClassTwoTwo implements Two
{
    public function __construct(One $one)
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

    public function methodOne($n)
    {
        return $n + 23;
    }
}

class FourBase implements Four
{
    public function __construct()
    {

    }

    public function methodOne()
    {
        global $one;
        $one = __CLASS__;
    }

    public function methodTwo()
    {
        global $two;
        $two = __CLASS__;
    }

    public function methodThree($n)
    {
        return $n + 23;
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

    public function methodOne($n)
    {
        return $n + 23;
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

interface Eight
{
    public function methodOne();

    public function methodTwo();

    public function methodThree();
}

class ClassEight implements Eight
{
    public static $called = array();
    public static $calledWith = array();

    public static function reset()
    {
        self::$called = array();
        self::$calledWith = array();
    }

    public function methodOne()
    {
        self::$called[] = 'methodOne';
    }

    public function methodTwo()
    {
        self::$called[] = 'methodTwo';
    }

    public function methodThree()
    {
        self::$called[] = 'methodThree';
    }

    public function methodFour()
    {
        self::$calledWith = func_get_args();
    }
}

class ClassEightExtension extends ClassEight
{
}

class ClassNine{
    public function __construct()
    {
        
    }
    public static function reset()
    {
        unset($GLOBALS['nine']);
    }

    public function methodOne()
    {
       $GLOBALS['nine']  = 'called';
    }    
}
