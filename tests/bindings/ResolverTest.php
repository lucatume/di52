<?php

class ResolverTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var tad_DI52_Container
     */
    protected $container;

    /**
     * @test
     * it should throw if trying to bind a non callable implementation
     */
    public function it_should_throw_if_trying_to_bind_a_non_callable_implementation()
    {
        $resolver = $this->makeInstance();

        $this->setExpectedException('InvalidArgumentException');

        $resolver->bind('TestInterfaceOne', 23);
    }

    private function makeInstance()
    {
        return new tad_DI52_Bindings_Resolver($this->container);
    }

    /**
     * @test
     * it should allow skipping extension check
     */
    public function it_should_allow_skipping_extension_check()
    {
        $resolver = $this->makeInstance();
        $resolver->bind('ConcreteClassImplementingTestInterfaceOne', 'ConcreteClassImplementingTestInterfaceTwo');
    }


    /**
     * @test
     * it should return an instance of the concrete class if trying to resolve a non bound concrete class alias
     */
    public function it_should_return_an_instance_of_the_concrete_class_if_trying_to_resolve_a_non_bound_concrete_class_alias(
    )
    {
        $sut = $this->makeInstance();

        $out = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');

        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceOne', $out);
    }

    /**
     * @test
     * it should throw if trying to resolve an existing non bound interface alias
     */
    public function it_should_throw_if_trying_to_resolve_an_existing_non_bound_interface_alias()
    {
        $resolver = $this->makeInstance();

        $this->setExpectedException('InvalidArgumentException');

        $resolver->resolve('TestInterfaceOne');
    }

    /**
     * @test
     * it should allow binding an object instance to an interface
     */
    public function it_should_allow_binding_an_object_instance_to_an_interface()
    {
        $sut = $this->makeInstance();

        $instance = new stdClass();
        $sut->bind('TestInterfaceOne', $instance);
        $out = $sut->resolve('TestInterfaceOne');

        $this->assertSame($instance, $out);
    }

    /**
     * @test
     * it should allow binding an object instance to a class
     */
    public function it_should_allow_binding_an_object_instance_to_a_class()
    {
        $sut = $this->makeInstance();

        $instance = new stdClass();
        $sut->bind('ConcreteClassImplementingTestInterfaceOne', $instance);
        $out = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');

        $this->assertSame($instance, $out);
    }

    /**
     * @test
     * it should resolve an interface dependency to a bound interface binding
     */
    public function it_should_resolve_an_interface_dependency_to_a_bound_interface_binding()
    {
        $sut = $this->makeInstance();

        $sut->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $out = $sut->resolve('DependingClassOne');

        $this->assertInstanceOf('DependingClassOne', $out);
        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceOne', $out->testInterface);
    }

    /**
     * @test
     * it should resolve a concrete class dependency to a bound concrete class binding
     */
    public function it_should_resolve_a_concrete_class_dependency_to_a_bound_concrete_class_binding()
    {
        $sut = $this->makeInstance();

        $sut->bind('ConcreteClassImplementingTestInterfaceOne', 'ExtendingClassOne');
        $out = $sut->resolve('DependingClassTwo');

        $this->assertInstanceOf('DependingClassTwo', $out);
        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceOne', $out->classOne);
        $this->assertInstanceOf('ExtendingClassOne', $out->classOne);
    }

    /**
     * @test
     * it should resolve a concrete class dependency to an unbound concrete class binding
     */
    public function it_should_resolve_a_concrete_class_dependency_to_an_unbound_concrete_class_binding()
    {
        $sut = $this->makeInstance();

        $out = $sut->resolve('DependingClassTwo');

        $this->assertInstanceOf('DependingClassTwo', $out);
        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceOne', $out->classOne);
    }

    /**
     * @test
     * it should throw if trying to solve an interface dependency to an unbound interface binding
     */
    public function it_should_throw_if_trying_to_solve_an_interface_dependency_to_an_unbound_interface_binding()
    {
        $sut = $this->makeInstance();

        $this->setExpectedException('InvalidArgumentException');

        $sut->resolve('DependingClassOne');
    }

    /**
     * @test
     * it should resolve primitive dependency to default value
     */
    public function it_should_resolve_primitive_dependency_to_default_value()
    {
        $sut = $this->makeInstance();

        $out = $sut->resolve('PrimitiveDependingClassOne');

        $this->assertEquals(23, $out->number);
    }

    /**
     * @test
     * it should throw if trying to resolve a primitive non defaulted dependency
     */
    public function it_should_throw_if_trying_to_resolve_a_primitive_non_defaulted_dependency()
    {
        $sut = $this->makeInstance();

        $this->setExpectedException('InvalidArgumentException');

        $sut->resolve('PrimitiveDependingClassTwo');
    }

    /**
     * @test
     * it should allow binding a class not implementing an interface to the interface
     */
    public function it_should_allow_binding_a_class_not_implementing_an_interface_to_the_interface()
    {
        $sut = $this->makeInstance();
        $sut->bind('TestInterfaceOne', 'ObjectOne');
        $out = $sut->resolve('TestInterfaceOne');

        $this->assertInstanceOf('ObjectOne', $out);
    }

    /**
     * @test
     * it should allow binding a class not extending a class to the class
     */
    public function it_should_allow_binding_a_class_not_extending_a_class_to_the_class()
    {
        $sut = $this->makeInstance();
        $sut->bind('ConcreteClassOne', 'ObjectOne');
        $out = $sut->resolve('ConcreteClassOne');

        $this->assertInstanceOf('ObjectOne', $out);
    }

    /**
     * @test
     * it should allow binding a singleton to an interface
     */
    public function it_should_allow_binding_a_singleton_to_an_interface()
    {
        $sut = $this->makeInstance();
        $sut->singleton('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $outOne = $sut->resolve('TestInterfaceOne');
        $outTwo = $sut->resolve('TestInterfaceOne');

        $this->assertSame($outOne, $outTwo);
    }

    /**
     * @test
     * it should allow binding a singleton to a class
     */
    public function it_should_allow_binding_a_singleton_to_a_class()
    {
        $sut = $this->makeInstance();
        $sut->singleton('ConcreteClassImplementingTestInterfaceOne', 'ExtendingClassOne');
        $outOne = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');
        $outTwo = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');

        $this->assertSame($outOne, $outTwo);
    }


    /**
     * @test
     * it should allow tagging an array of implementations
     */
    public function it_should_allow_tagging_an_array_of_implementations()
    {
        $container = $this->makeInstance();

        $container->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $container->bind('TestInterfaceTwo', 'ConcreteClassImplementingTestInterfaceTwo');

        $container->tag(array('TestInterfaceOne', 'TestInterfaceTwo'), 'tag1');

        $out = $container->tagged('tag1');

        $this->assertInternalType('array', $out);
        $this->assertCount(2, $out);
        $this->assertInstanceOf('TestInterfaceOne', $out[0]);
        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceOne', $out[0]);
        $this->assertInstanceOf('TestInterfaceTwo', $out[1]);
        $this->assertInstanceOf('ConcreteClassImplementingTestInterfaceTwo', $out[1]);
    }

    /**
     * @test
     * it should throw if tag is not a string while tagging
     */
    public function it_should_throw_if_tag_is_not_a_string_while_tagging()
    {
        $container = $this->makeInstance();

        $this->setExpectedException('InvalidArgumentException');

        $container->tag(array('TestInterfaceOne', 'TestInterfaceTwo'), 23);
    }

    /**
     * @test
     * it should throw if tag is not a string while retrieving tagged
     */
    public function it_should_throw_if_tag_is_not_a_string_while_retrieving_tagged()
    {
        $container = $this->makeInstance();

        $container->bind('TestInterfaceOne', 'ConcreteClassImplementingTestInterfaceOne');
        $container->bind('TestInterfaceTwo', 'ConcreteClassImplementingTestInterfaceTwo');

        $container->tag(array('TestInterfaceOne', 'TestInterfaceTwo'), 'tag1');

        $this->setExpectedException('InvalidArgumentException');

        $container->tagged(23);
    }

    /**
     * @test
     * it should allow querying for a bound implementation
     */
    public function it_should_allow_querying_for_a_bound_implementation()
    {
        $container = $this->makeInstance();

        $this->assertFalse($container->isBound('TestInterfaceOne'));

        $container->bind('TestInterfaceOne', 'ClassOne');

        $this->assertTrue($container->isBound('TestInterfaceOne'));
    }

    /**
     * @test
     * it should allow querying for a bound singleton implementation
     */
    public function it_should_allow_querying_for_a_bound_singleton_implementation()
    {
        $container = $this->makeInstance();

        $this->assertFalse($container->isBound('TestInterfaceOne'));

        $container->singleton('TestInterfaceOne', 'ClassOne');

        $this->assertTrue($container->isBound('TestInterfaceOne'));
    }

    /**
     * @test
     * it should throw if trying to check for bound non string
     */
    public function it_should_throw_if_trying_to_check_for_bound_non_string()
    {
        $this->setExpectedException('InvalidArgumentException');

        $container = $this->makeInstance();

        $container->isBound(23);
    }

    /**
     * @test
     * it should throw if trying to check for tagged non string
     */
    public function it_should_throw_if_trying_to_check_for_tagged_non_string()
    {
        $this->setExpectedException('InvalidArgumentException');

        $container = $this->makeInstance();

        $container->hasTag(23);
    }

    /**
     * @test
     * it should allow checking for tags
     */
    public function it_should_allow_checking_for_tags()
    {
        $container = $this->makeInstance();

        $this->assertFalse($container->hasTag('some-tag'));

        $container->bind('TestInterfaceOne', 'ClassOne');
        $container->bind('TestInterfaceTwo', 'ClassTwo');
        $container->tag(array('TestInterfaceOne', 'TestInterfaceTwo'), 'some-tag');

        $this->assertTrue($container->hasTag('some-tag'));
    }

    /**
     * @test
     * it should resolve singleton bindings of different interfaces with same implementation to same instance
     */
    public function it_should_resolve_singleton_bindings_of_different_interfaces_with_same_implementation_to_same_instance(
    )
    {
        $container = $this->makeInstance();

        $container->singleton('TestInterfaceOne', 'InterfaceOneAndTwoImplementation');
        $container->singleton('TestInterfaceTwo', 'InterfaceOneAndTwoImplementation');

        $instanceOne = $container->resolve('TestInterfaceOne');
        $instanceTwo = $container->resolve('TestInterfaceTwo');

        $this->assertSame($instanceOne, $instanceTwo);
    }

    /**
     * @test
     * it should resolve singleton bindings of different class and interface with same implementation to same instance
     */
    public function it_should_resolve_singleton_bindings_of_different_class_and_interface_with_same_implementation_to_same_instance(
    )
    {
        $container = $this->makeInstance();

        $container->singleton('TestInterfaceOne', 'InterfaceOneAndTwoImplementation');
        $container->singleton('ClassOne', 'InterfaceOneAndTwoImplementation');

        $instanceOne = $container->resolve('TestInterfaceOne');
        $instanceTwo = $container->resolve('ClassOne');

        $this->assertSame($instanceOne, $instanceTwo);
    }

    /**
     * @test
     * it should allow binding a decorator chain using an array
     */
    public function it_should_allow_binding_a_decorator_chain_using_an_array()
    {
        $container = $this->makeInstance();

        $decorators = array('BaseClassDecoratorThree', 'BaseClassDecoratorTwo', 'BaseClassDecoratorOne', 'BaseClass');
        $container->bindDecorators('BaseClassInterface', $decorators);

        $instance = $container->resolve('BaseClassInterface');

        $this->assertInstanceOf('BaseClassDecoratorThree', $instance);
    }

    /**
     * @test
     * it should allow binding a decorator chain as a singleton using singletonDecorators
     */
    public function it_should_allow_binding_a_decorator_chain_as_a_singleton_using_singletonDecorators()
    {
        $container = $this->makeInstance();

        $decorators = array('BaseClassDecoratorThree', 'BaseClassDecoratorTwo', 'BaseClassDecoratorOne', 'BaseClass');
        $container->singletonDecorators('BaseClassInterface', $decorators);

        $instance = $container->resolve('BaseClassInterface');
        $instance2 = $container->resolve('BaseClassInterface');

        $this->assertSame($instance, $instance2);
    }

    /**
     * @test
     * it should allow binding a class to itself
     */
    public function it_should_allow_binding_a_class_to_itself()
    {
        $sut = $this->makeInstance();

        $sut->singleton('ClassOne', 'ClassOne');

        $this->assertSame($sut->resolve('ClassOne'), $sut->resolve('ClassOne'));
    }

    /**
     * @test
     * it should allow binding a class by slug
     */
    public function it_should_allow_binding_a_class_by_slug()
    {
        $sut = $this->makeInstance();

        $sut->bind('c.one', 'ClassOne');
        $sut->bind('c.base', 'BaseClass');

        $this->assertInstanceOf('ClassOne', $sut->resolve('c.one'));
        $this->assertNotSame($sut->resolve('c.one'), $sut->resolve('c.one'));
        $this->assertInstanceOf('BaseClass', $sut->resolve('c.base'));
        $this->assertNotSame($sut->resolve('c.base'), $sut->resolve('c.base'));
    }

    /**
     * @test
     * it should return a different instance on each call of a class bound by slug
     */
    public function it_should_return_a_different_instance_on_each_call_of_a_class_bound_by_slug()
    {
        $sut = $this->makeInstance();

        $sut->bind('c.one', 'ClassOne');

        $this->assertNotSame($sut->resolve('c.one'), $sut->resolve('c.one'));
    }

    /**
     * @test
     * it should allow binding singletons by slug
     */
    public function it_should_allow_binding_singletons_by_slug()
    {
        $sut = $this->makeInstance();

        $sut->singleton('c.one', 'ClassOne');
        $sut->singleton('c.base', 'BaseClass');

        $this->assertInstanceOf('ClassOne', $sut->resolve('c.one'));
        $this->assertSame($sut->resolve('c.one'), $sut->resolve('c.one'));
        $this->assertInstanceOf('BaseClass', $sut->resolve('c.base'));
        $this->assertSame($sut->resolve('c.base'), $sut->resolve('c.base'));
    }

    /**
     * @test
     * it should return same instance when binding singletons by slug
     */
    public function it_should_return_same_instance_when_binding_singletons_by_slug()
    {
        $sut = $this->makeInstance();

        $sut->singleton('c.one', 'ClassOne');

        $this->assertSame($sut->resolve('c.one'), $sut->resolve('c.one'));
    }

    /**
     * @test
     * it should allow binding objects by slug
     */
    public function it_should_allow_binding_objects_by_slug()
    {
        $sut = $this->makeInstance();

        $sut->bind('c.one', new ClassOne());

        $this->assertSame($sut->resolve('c.one'), $sut->resolve('c.one'));
    }

    /**
     * @test
     * it should allow binding objects as singletons by slug
     */
    public function it_should_allow_binding_objects_as_singletons_by_slug()
    {
        $sut = $this->makeInstance();

        $sut->singleton('c.one', new ClassOne());

        $this->assertSame($sut->resolve('c.one'), $sut->resolve('c.one'));
    }

    /**
     * @test
     * it should resolve dependencies bound using slugs when requested
     */
    public function it_should_resolve_dependencies_bound_using_slugs_when_requested()
    {
        $sut = $this->makeInstance();

        $sut->singleton('c.one', 'ClassOne');
        $sut->singleton('c.requiringOne', 'RequiringOne');

        $requiringOne = $sut->resolve('c.requiringOne');
        $this->assertInstanceOf('RequiringOne', $requiringOne);
        $this->assertSame($requiringOne->getOne(), $sut->resolve('c.one'));
    }

    /**
     * @test
     * it should resolve non singleton dependencies bound using slugs when requested
     */
    public function it_should_resolve_non_singleton_dependencies_bound_using_slugs_when_requested()
    {
        $sut = $this->makeInstance();

        $sut->bind('c.one', 'ClassOneWithCounter');
        $sut->bind('c.requiringOneWithCounter', 'RequiringOneWithCounter');

        $this->assertInstanceOf('RequiringOne', $sut->resolve('c.requiringOneWithCounter'));
        $i1 = $sut->resolve('c.requiringOneWithCounter');
        $this->assertEquals(2, $i1->getOne()->getVar());
        $i2 = $sut->resolve('c.requiringOneWithCounter');
        $this->assertEquals(3, $i2->getOne()->getVar());
        $this->assertNotSame($i1, $i2);
    }

    /**
     * @test
     * it should allow registering methods to call after build
     */
    public function it_should_allow_registering_methods_to_call_after_build()
    {
        $sut = $this->makeInstance();
        ClassOne::reset();

        $sut->bind('one', 'ClassOne', array('methodOne', 'methodTwo', 'methodThree'));

        $i = $sut->resolve('one');
        $this->assertEquals(1, $i->getMethodOneCalled());
        $this->assertEquals(1, $i->getMethodTwoCalled());
        $this->assertEquals(1, $i->getMethodThreeCalled());
    }

    /**
     * @test
     * it should call after build methods on each new instance if not singleton
     */
    public function it_should_call_after_build_methods_on_each_new_instance_if_not_singleton()
    {
        $sut = $this->makeInstance();
        ClassOne::reset();

        $sut->bind('one', 'ClassOne', array('methodOne', 'methodTwo', 'methodThree'));

        $i1 = $sut->resolve('one');
        $this->assertEquals(1, $i1->getMethodOneCalled());
        $this->assertEquals(1, $i1->getMethodTwoCalled());
        $this->assertEquals(1, $i1->getMethodThreeCalled());

        $i2 = $sut->resolve('one');
        $this->assertEquals(2, $i2->getMethodOneCalled());
        $this->assertEquals(2, $i2->getMethodTwoCalled());
        $this->assertEquals(2, $i2->getMethodThreeCalled());
    }

    /**
     * @test
     * it should call after build methods on singletons
     */
    public function it_should_call_after_build_methods_on_singletons()
    {
        $sut = $this->makeInstance();
        ClassOne::reset();

        $sut->singleton('one', 'ClassOne', array('methodOne', 'methodTwo', 'methodThree'));

        $i1 = $sut->resolve('one');
        $this->assertEquals(1, $i1->getMethodOneCalled());
        $this->assertEquals(1, $i1->getMethodTwoCalled());
        $this->assertEquals(1, $i1->getMethodThreeCalled());
    }

    /**
     * @test
     * it should call after builds methods on singleton just once
     */
    public function it_should_call_after_builds_methods_on_singleton_just_once()
    {
        $sut = $this->makeInstance();
        ClassOne::reset();

        $sut->singleton('one', 'ClassOne', array('methodOne', 'methodTwo', 'methodThree'));

        $i1 = $sut->resolve('one');
        $this->assertEquals(1, $i1->getMethodOneCalled());
        $this->assertEquals(1, $i1->getMethodTwoCalled());
        $this->assertEquals(1, $i1->getMethodThreeCalled());

        $i2 = $sut->resolve('one');
        $this->assertSame($i1, $i2);
        $this->assertEquals(1, $i2->getMethodOneCalled());
        $this->assertEquals(1, $i2->getMethodTwoCalled());
        $this->assertEquals(1, $i2->getMethodThreeCalled());
    }

    /**
     * @test
     * it should allow re-registering a bound implementation
     */
    public function it_should_allow_re_registering_a_bound_implementation()
    {
        $sut = $this->makeInstance();

        $sut->bind('TestInterfaceOne', 'ClassOne');

        $sut->bind('foo', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->resolve('foo'));

        $sut->replaceBind('foo', 'ClassTwo');

        $this->assertInstanceOf('ClassTwo', $sut->resolve('foo'));
    }

    /**
     * @test
     * it should allow re-registering a singleton bound implementation
     */
    public function it_should_allow_re_registering_a_singleton_bound_implementation()
    {
        $sut = $this->makeInstance();

        $sut->bind('TestInterfaceOne', 'ClassOne');

        $sut->singleton('foo', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->resolve('foo'));

        $sut->replaceSingleton('foo', 'ClassTwo');

        $this->assertInstanceOf('ClassTwo', $sut->resolve('foo'));
    }

    protected function setUp()
    {
        $this->container = $this->getMock('tad_DI52_Container');
    }
}
