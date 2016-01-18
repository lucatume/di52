<?php

use tad_DI52_Container as DI;

class ArrayAccessTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should implement the ArrayAccess interface
     */
    public function it_should_implement_the_array_access_interface()
    {
        $this->assertInstanceOf('ArrayAccess', new DI());
    }

    /**
     * @test
     * it should allow setting a shared instance using array access
     */
    public function it_should_allow_setting_a_shared_instance_using_array_access()
    {
        $sut = new DI();
        $sut['one'] = 'arrayAccessDummyWithNoConstructor';

        $i1 = $sut['one'];
        $i2 = $sut['one'];

        $this->assertSame($i1, $i2);
    }

    /**
     * @test
     * it should allow setting a shared instance and args using array access
     */
    public function it_should_allow_setting_a_shared_instance_and_args_using_array_access()
    {
        $sut = new DI();
        $sut['one'] = ['arrayAccessDummy', 4, 5];

        $i1 = $sut['one'];
        $i2 = $sut['one'];

        $this->assertSame($i1, $i2);
        $this->assertEquals(9, $i1->add());
    }

    /**
     * @test
     * it should allow setting a var using array access
     */
    public function it_should_allow_setting_a_var_using_array_access()
    {
        $sut = new DI();

        $sut['var'] = 'foo';

        $this->assertEquals('foo', $sut['var']);
    }

    /**
     * @test
     * it should unset a constructor
     */
    public function it_should_unset_a_constructor()
    {
        $sut = new DI();
        $sut['one'] = 'arrayAccessDummyWithNoConstructor';

        unset($sut['one']);

        $this->setExpectedException('InvalidArgumentException');
        $sut['one'];
    }

    /**
     * @test
     * it should unset a var
     */
    public function it_should_unset_a_var()
    {
        $sut = new DI();
        $sut['one'] = 'foo';

        unset($sut['one']);

        $this->setExpectedException('InvalidArgumentException');
        $sut['one'];
    }

    /**
     * @test
     * it should allow for lazy instantiation
     */
    public function it_should_allow_for_lazy_instantiation()
    {
        $sut = new DI();
        global $_foo;
        $_foo = 1;
        $sut['dependency'] = 'arrayAccessDummyDependency';
        $sut['some-class'] = array('arrayAccessDummyTwo', '@dependency');

        $_foo = 2;

        $var = $sut['some-class']->get_var();

        $this->assertEquals(2, $var);
    }
}

class arrayAccessDummyWithNoConstructor
{
}

class arrayAccessDummy
{
    private $one;
    private $two;

    public function __construct($one, $two)
    {

        $this->one = $one;
        $this->two = $two;
    }

    public function add()
    {
        return $this->one + $this->two;
    }
}

class arrayAccessDummyTwo
{
    /**
     * @var arrayAccessDummyDependency
     */
    private $accessDummyDependency;

    public function __construct(arrayAccessDummyDependency $accessDummyDependency)
    {

        $this->accessDummyDependency = $accessDummyDependency;
    }

    public function get_var()
    {
        return $this->accessDummyDependency->get_var();
    }
}

class arrayAccessDummyDependency
{
    protected $var;

    public function __construct()
    {
        global $_foo;
        $this->var = $_foo;
    }

    public function get_var()
    {
        return $this->var;
    }
}
