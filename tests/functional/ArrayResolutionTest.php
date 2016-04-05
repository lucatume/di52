<?php

use tad_DI52_Container as DI;

class Dummy23423424
{

}

class ArrayResolutionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should allow setting an array of variables and resolve them
     */
    public function it_should_allow_setting_an_array_of_variables_and_resolve_them()
    {
        $container = new DI();
        $container['var-one'] = 'foo';
        $container['var-two'] = 'baz';
        $container['var-three'] = 'bar';

        $container['a-list-of-vars'] = array('#var-one', '#var-two', '#var-three');

        $this->assertInternalType('array', $container['a-list-of-vars']);
        $this->assertEquals('foo', $container['a-list-of-vars'][0]);
        $this->assertEquals('baz', $container['a-list-of-vars'][1]);
        $this->assertEquals('bar', $container['a-list-of-vars'][2]);
    }

    /**
     * @test
     * it should allow setting an array of constructors and resolve them
     */
    public function it_should_allow_setting_an_array_of_constructors_and_resolve_them()
    {
        $container = new DI();
        $container['ctor-one'] = 'Dummy23423424';
        $container['ctor-two'] = 'Dummy23423424';
        $container['ctor-three'] = 'Dummy23423424';

        $container['a-list-of-ctors'] = array('@ctor-one', '@ctor-two', '@ctor-three');

        $this->assertInternalType('array', $container['a-list-of-ctors']);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-ctors'][0]);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-ctors'][1]);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-ctors'][2]);
    }

    /**
     * @test
     * it should allow setting and array of variables and constructors and resolve them
     */
    public function it_should_allow_setting_and_array_of_variables_and_constructors_and_resolve_them()
    {
        $container = new DI();
        $container['ctor-one'] = 'Dummy23423424';
        $container['ctor-two'] = 'Dummy23423424';
        $container['var-one'] = 'foo';
        $container['var-two'] = 'baz';

        $container['a-list-of-stuff'] = array('@ctor-one', '@ctor-two', '#var-one', '#var-two');

        $this->assertInternalType('array', $container['a-list-of-stuff']);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-stuff'][0]);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-stuff'][1]);
        $this->assertEquals('foo', $container['a-list-of-stuff'][2]);
        $this->assertEquals('baz', $container['a-list-of-stuff'][3]);
    }

    /**
     * @test
     * it should allow mixing ctors, vars and real values
     */
    public function it_should_allow_mixing_ctors_vars_and_real_values()
    {
        $container = new DI();
        $container['ctor-one'] = 'Dummy23423424';
        $container['ctor-two'] = 'Dummy23423424';
        $container['var-one'] = 'foo';
        $container['var-two'] = 'baz';

        $container['a-list-of-stuff'] = array('@ctor-one', '@ctor-two', '#var-one', '#var-two', 'just a string', 23);

        $this->assertInternalType('array', $container['a-list-of-stuff']);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-stuff'][0]);
        $this->assertInstanceOf('Dummy23423424', $container['a-list-of-stuff'][1]);
        $this->assertEquals('foo', $container['a-list-of-stuff'][2]);
        $this->assertEquals('baz', $container['a-list-of-stuff'][3]);
        $this->assertEquals('just a string', $container['a-list-of-stuff'][4]);
        $this->assertEquals(23, $container['a-list-of-stuff'][5]);
    }
}
