<?php

class ContainerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var tad_DI52_Container
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new tad_DI52_Container();
    }

    /**
     * @test
     * it should be instantiatable
     */
    public function it_should_be_instantiatable()
    {
        $this->assertInstanceOf('tad_DI52_Container', $this->sut);
    }

    /**
     * @test
     * it should allow registering a variable
     */
    public function it_should_allow_registering_a_variable()
    {
        $this->sut->setVar('foo', 23);

        $this->assertEquals(23, $this->sut->getVar('foo'));
    }

    /**
     * @test
     * it should allow setting a null value
     */
    public function it_should_allow_setting_a_null_value()
    {
        $this->sut->setVar('foo');

        $this->assertNull($this->sut->getVar('foo'));
    }

    /**
     * @test
     * it should not allow setting a variable a second time
     */
    public function it_should_not_allow_setting_a_variable_a_second_time()
    {
        $this->sut->setVar('foo', 23);

        $this->assertEquals(23, $this->sut->getVar('foo'));
        $this->sut->setVar('foo', 'new value');

        $this->assertEquals(23, $this->sut->getVar('foo'));
    }

    /**
     * @test
     * it should throw if trying to get non set var
     */
    public function it_should_throw_if_trying_to_get_non_set_var()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->sut->getVar('foo');
    }

    /**
     * @test
     * it should allow registering a constructor
     */
    public function it_should_allow_registering_a_constructor()
    {
        $this->sut->setCtor('object', 'ObjectOne');

        $object = $this->sut->make('object');

        $this->assertInstanceOf('ObjectOne', $object);
    }

    /**
     * @test
     * it should return a new instance of an object on each make call
     */
    public function it_should_return_a_new_instance_of_an_object_on_each_make_call()
    {
        $this->sut->setCtor('object', 'ObjectOne');

        $object1 = $this->sut->make('object');
        $object2 = $this->sut->make('object');

        $this->assertNotSame($object1, $object2);
    }

    /**
     * @test
     * it should allow specifying a constructor method
     */
    public function it_should_allow_specifying_a_constructor_method()
    {
        $class = 'ObjectOne';

        $this->sut->setCtor('object', $class . '::create');

        $object = $this->sut->make('object');

        $this->assertInstanceOf('ObjectOne', $object);
    }

    /**
     * @test
     * it should allow specifying constructor arguments
     */
    public function it_should_allow_specifying_constructor_arguments()
    {
        $class = 'ObjectTwo';

        $this->sut->setCtor('object', $class, 'foo', 23);

        $object = $this->sut->make('object');

        $this->assertInstanceOf($class, $object);
        $this->assertEquals('foo', $object->string);
        $this->assertEquals(23, $object->int);
    }

    /**
     * @test
     * it should allow specifying static constructor arguments
     */
    public function it_should_allow_specifying_static_constructor_arguments()
    {
        $class = 'ObjectThree';

        $this->sut->setCtor('object', $class . '::one', 'foo', 23);

        $object = $this->sut->make('object');

        $this->assertInstanceOf($class, $object);
        $this->assertEquals('foo', $object->string);
        $this->assertEquals(23, $object->int);
    }

    /**
     * @test
     * it should allow specifying previously registered vars as args
     */
    public function it_should_allow_specifying_previously_registered_vars_as_args()
    {
        $class = 'ObjectThree';
        $this->sut->setVar('string', 'foo');
        $this->sut->setVar('int', 23);

        $this->sut->setCtor('object', $class . '::one', '#string', '#int');

        $object = $this->sut->make('object');

        $this->assertInstanceOf($class, $object);
        $this->assertEquals('foo', $object->string);
        $this->assertEquals(23, $object->int);
    }

    /**
     * @test
     * it should allow specifying previously registered objects as args
     */
    public function it_should_allow_specifying_previously_registered_objects_as_args()
    {
        $class = 'ObjectFour';

        $this->sut->setCtor('myObject', 'ObjectOne');
        $this->sut->setVar('string', 'foo');

        $this->sut->setCtor('dependingObject', 'ObjectFour::create', '@myObject', '#string');

        $object = $this->sut->make('dependingObject');

        $this->assertInstanceOf($class, $object);
        $this->assertInstanceOf('ObjectOne', $object->myObject);
        $this->assertEquals('foo', $object->string);
    }

    /**
     * @test
     * it should allow setting a singleton instance
     */
    public function it_should_allow_setting_a_singleton_instance()
    {
        $class = 'ObjectOne';

        $this->sut->setShared('singleton', $class);

        $i1 = $this->sut->make('singleton');
        $i2 = $this->sut->make('singleton');

        $this->assertSame($i1, $i2);
    }

    /**
     * @test
     * it should allow setting a singleton instance using set vars
     */
    public function it_should_allow_setting_a_singleton_instance_using_set_vars()
    {
        $class = 'ObjectTwo';

        $this->sut->setVar('string', 'foo');
        $this->sut->setVar('int', 23);

        $this->sut->setShared('singleton', $class, '#string', '#int');

        $i1 = $this->sut->make('singleton');
        $i2 = $this->sut->make('singleton');

        $this->assertSame($i1, $i2);
    }

    /**
     * @test
     * it should allow setting a singleton using set instances
     */
    public function it_should_allow_setting_a_singleton_using_set_instances()
    {
        $class = 'ObjectFour';

        $this->sut->setCtor('myObject', 'ObjectOne::create');

        $this->sut->setShared('singleton', $class . '::create', '@myObject', 'foo');

        $i1 = $this->sut->make('singleton');
        $i2 = $this->sut->make('singleton');

        $this->assertSame($i1, $i2);
    }

    /**
     * @test
     * it should allow to set methods to be called after the contstructor method
     */
    public function it_should_allow_to_set_methods_to_be_called_after_the_contstructor_method()
    {
        $class = 'ObjectFive';

        $this->sut->setCtor('object', $class)
            ->setDependency(new DependencyObjectOne())
            ->setString('foo')
            ->setInt(23);
        $i = $this->sut->make('object');

        $this->assertInstanceOf($class, $i);
        $this->assertInstanceOf('DependencyObjectOne', $i->dependency);
        $this->assertEquals('foo', $i->string);
        $this->assertEquals(23, $i->int);
    }

    /**
     * @test
     * it should allow specifying methods to call after constructors and refer previuosly registered arguments
     */
    public function it_should_allow_specifying_methods_to_call_after_constructors_and_refer_previuosly_registered_arguments()
    {
        $class = 'ObjectFive';

        $this->sut->setCtor('dependency', 'DependencyObjectOne');
        $this->sut->setVar('string', 'foo');
        $this->sut->setVar('int', 23);
        $this->sut->setCtor('object', $class)->setDependency('@dependency')->setString('#string')
            ->setInt('#int');

        $i = $this->sut->make('object');

        $this->assertInstanceOf($class, $i);
        $this->assertInstanceOf('DependencyObjectOne', $i->dependency);
        $this->assertEquals('foo', $i->string);
        $this->assertEquals(23, $i->int);
    }

    /**
     * @test
     * it should allow calling a static constructor with dependencies and call set methods after
     */
    public function it_should_allow_calling_a_static_constructor_with_dependencies_and_call_set_methods_after()
    {
        $class = 'ObjectFive';

        $this->sut->setCtor('dependency', 'DependencyObjectOne');
        $this->sut->setVar('string', 'foo');
        $this->sut->setVar('int', 23);

        $this->sut->setCtor('object', $class . '::makeOne', '@dependency')
            ->setString('#string')
            ->setInt('#int');

        $i = $this->sut->make('object');

        $this->assertInstanceOf($class, $i);
        $this->assertInstanceOf('DependencyObjectOne', $i->dependency);
        $this->assertEquals('foo', $i->string);
        $this->assertEquals(23, $i->int);
    }

    /**
     * @test
     * it should allow calling methods on the made object using call_method
     */
    public function it_should_allow_calling_methods_on_the_made_object_using_call_method()
    {
        $class = 'ObjectFive';

        $this->sut->setCtor('dependency', 'DependencyObjectOne');
        $this->sut->setVar('string', 'foo');
        $this->sut->setVar('int', 23);

        $this->sut->setCtor('object', $class . '::makeOne', '@dependency')
            ->callMethod('setString', '#string')
            ->callMethod('setInt', '#int');

        $i = $this->sut->make('object');

        $this->assertInstanceOf($class, $i);
        $this->assertInstanceOf('DependencyObjectOne', $i->dependency);
        $this->assertEquals('foo', $i->string);
        $this->assertEquals(23, $i->int);
    }

    /**
     * @test
     * it should allow not specifying the class of simple objects
     */
    public function it_should_allow_not_specifying_the_class_of_simple_objects()
    {
        // not specifying a ctor method for tad_Dependency
        // $this->sut->setCtor( 'dependency', 'DependencyObjectOne' );
        $this->sut->setVar('string', 'foo');
        $this->sut->setVar('int', 23);

        $class = 'DependingClassThree';
        $dependencyClass = 'ConcreteClassOne';
        $this->sut->setCtor('object', $class, '~' . $dependencyClass);

        $i = $this->sut->make('object');

        $this->assertInstanceOf($class, $i);
        $this->assertInstanceOf($dependencyClass, $i->classOne);
    }

    /**
     * @test
     * it should allow re-registering a bound implementation
     */
    public function it_should_allow_re_registering_a_bound_implementation()
    {
        $this->sut->bind('TestInterfaceOne', 'ClassOne');

        $this->sut->bind('foo', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $this->sut->make('foo'));

        $this->sut->replaceBind('foo', 'ClassTwo');

        $this->assertInstanceOf('ClassTwo', $this->sut->make('foo'));
    }

    /**
     * @test
     * it should allow re-registering a singleton bound implementation
     */
    public function it_should_allow_re_registering_a_singleton_bound_implementation()
    {
        $this->sut->bind('TestInterfaceOne', 'ClassOne');

        $this->sut->singleton('foo', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $this->sut->make('foo'));

        $this->sut->replaceSingleton('foo', 'ClassTwo');

        $this->assertInstanceOf('ClassTwo', $this->sut->make('foo'));
    }
}
