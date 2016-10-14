<?php

class ClassOne implements TestInterfaceOne
{
    protected static $methodOneCalled = 0;
    protected static $methodTwoCalled = 0;
    protected static $methodThreeCalled = 0;
    protected $var = 1;

    public static function reset()
    {
        self::$methodOneCalled = 0;
        self::$methodTwoCalled = 0;
        self::$methodThreeCalled = 0;
    }

    /**
     * @return int
     */
    public function getVar()
    {
        return $this->var;
    }

    /**
     * @param int $var
     */
    public function setVar($var)
    {
        $this->var = $var;
    }


    public function methodOne()
    {
        self::$methodOneCalled += 1;
    }

    public function methodTwo()
    {

        self::$methodTwoCalled += 1;
    }

    public function methodThree()
    {

        self::$methodThreeCalled += 1;
    }

    /**
     * @return int
     */
    public function getMethodThreeCalled()
    {
        return self::$methodThreeCalled;
    }

    /**
     * @return int
     */
    public function getMethodTwoCalled()
    {
        return self::$methodTwoCalled;
    }

    /**
     * @return int
     */
    public function getMethodOneCalled()
    {
        return self::$methodOneCalled;
    }
}