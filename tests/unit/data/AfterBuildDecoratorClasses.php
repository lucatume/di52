<?php
interface ZorpMaker
{
    public function setupTheZorps();

    public function makeZorps();
}

class AfterBuildDecoratorOne implements ZorpMaker
{
    public static $didSetUpTheZorps = false;
    private $decorated;

    public static function reset()
    {
        self::$didSetUpTheZorps = false;
    }

    public function __construct(ZorpMaker $decorated)
    {
        $this->decorated = $decorated;
    }

    public function setupTheZorps()
    {
        self::$didSetUpTheZorps = true;
    }

    public function makeZorps()
    {
        return '1 - ' . $this->decorated->makeZorps();
    }
}

class AfterBuildDecoratorTwo implements ZorpMaker
{
    public static $didSetUpTheZorps = false;
    private $decorated;

    public static function reset()
    {
        self::$didSetUpTheZorps = false;
    }

    public function __construct(ZorpMaker $decorated)
    {
        $this->decorated = $decorated;
    }

    public function setupTheZorps()
    {
        self::$didSetUpTheZorps = true;
    }

    public function makeZorps()
    {
        return '2 - ' . $this->decorated->makeZorps();
    }
}

class AfterBuildDecoratorThree implements ZorpMaker
{
    public static $didSetUpTheZorps = false;
    private $decorated;

    public static function reset()
    {
        self::$didSetUpTheZorps = false;
    }

    public function __construct(ZorpMaker $decorated)
    {
        $this->decorated = $decorated;
    }

    public function setupTheZorps()
    {
        self::$didSetUpTheZorps = true;
    }

    public function makeZorps()
    {
        return '3 - ' . $this->decorated->makeZorps();
    }
}

class AfterBuildBase implements ZorpMaker
{
    public static $didSetUpTheZorps = false;

    public static function reset()
    {
        self::$didSetUpTheZorps = false;
    }

    public function setupTheZorps()
    {
        self::$didSetUpTheZorps = true;
    }

    public function makeZorps()
    {
        return 'base';
    }
}
