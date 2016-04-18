<?php
use tad_DI52_Bindings_Resolver as Resolver;

class ResolverTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var tad_DI52_Container
     */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->prophesize('tad_DI52_Container');
    }

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

    /**
     * @test
     * it should allow skipping extension check
     */
    public function it_should_allow_skipping_extension_check()
    {
        $resolver = $this->makeInstance();
        $resolver->bind('ConcreteClassImplementingTestInterfaceOne', 'ConcreteClassImplementingTestInterfaceTwo', true);
    }

    /**
     * @test
     * it should allow binding a callback to an interface
     */
    public function it_should_allow_binding_a_callback_to_an_interface()
    {
        $resolver = $this->makeInstance();

        $object = (object)['foo' => 'bar'];
        $callback = function () use ($object) {
            return $object;
        };

        $resolver->bind('TestInterfaceOne', $callback);

        $out = $resolver->resolve('TestInterfaceOne');
        $this->assertEquals('bar', $out->foo);

        $object->foo = 'baz';

        $out = $resolver->resolve('TestInterfaceOne');
        $this->assertEquals('baz', $out->foo);
    }

    /**
     * @test
     * it should rerun the callback on each resolution
     */
    public function it_should_rerun_the_callback_on_each_resolution()
    {
        $resolver = $this->makeInstance();

        $callback = function () {
            return microtime();
        };

        $resolver->bind('TestInterfaceOne', $callback);
        $one = $resolver->resolve('TestInterfaceOne');
        $two = $resolver->resolve('TestInterfaceOne');

        $this->assertNotEquals($one, $two);
    }

    /**
     * @test
     * it should return an instance of the concrete class if trying to resolve a non bound concrete class alias
     */
    public function it_should_return_an_instance_of_the_concrete_class_if_trying_to_resolve_a_non_bound_concrete_class_alias()
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

        $this->setExpectedException('Exception');

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

        $this->setExpectedException('Exception');

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

        $out = $sut->resolve('PrimitiveDependingClassTwo');
    }

    /**
     * @test
     * it should allow binding a class not implementing an interface to the interface
     */
    public function it_should_allow_binding_a_class_not_implementing_an_interface_to_the_interface()
    {
        $sut = $this->makeInstance();
        $sut->bind('TestInterfaceOne', 'ObjectOne', true);
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
        $sut->bind('ConcreteClassOne', 'ObjectOne', true);
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
     * it should allow binding a singleton callback to an interface
     */
    public function it_should_allow_binding_a_singleton_callback_to_an_interface()
    {
        $sut = $this->makeInstance();
        $sut->singleton('TestInterfaceOne', function () {
            return microtime();
        });
        $outOne = $sut->resolve('TestInterfaceOne');
        $outTwo = $sut->resolve('TestInterfaceOne');

        $this->assertSame($outOne, $outTwo);
    }

    /**
     * @test
     * it should allow binding a singleton callback to a class
     */
    public function it_should_allow_binding_a_singleton_callback_to_a_class()
    {
        $sut = $this->makeInstance();
        $sut->singleton('ConcreteClassImplementingTestInterfaceOne', function () {
            return microtime();
        });
        $outOne = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');
        $outTwo = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');

        $this->assertSame($outOne, $outTwo);
    }

    private function makeInstance()
    {
        return new Resolver($this->container->reveal());
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

        $container->tag(['TestInterfaceOne', 'TestInterfaceTwo'], 'tag1');

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

        $container->tag(['TestInterfaceOne', 'TestInterfaceTwo'], 23);
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

        $container->tag(['TestInterfaceOne', 'TestInterfaceTwo'], 'tag1');

        $this->setExpectedException('InvalidArgumentException');

        $out = $container->tagged(23);
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
        $container->tag(['TestInterfaceOne', 'TestInterfaceTwo'], 'some-tag');

        $this->assertTrue($container->hasTag('some-tag'));
    }

    /**
     * @test
     * it should resolve singleton bindings of different interfaces with same implementation to same instance
     */
    public function it_should_resolve_singleton_bindings_of_different_interfaces_with_same_implementation_to_same_instance()
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
    public function it_should_resolve_singleton_bindings_of_different_class_and_interface_with_same_implementation_to_same_instance()
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
     * it should resolve singleton bindings of different interfaces with same implementation to same callback
     */
    public function it_should_resolve_singleton_bindings_of_different_interfaces_with_same_implementation_to_same_callback()
    {
        $container = $this->makeInstance();

        $f = function () {
            return microtime();
        };

        $container->singleton('TestInterfaceOne', $f);
        $container->singleton('TestInterfaceTwo', $f);

        $outputOne = $container->resolve('TestInterfaceOne');
        $outputTwo = $container->resolve('TestInterfaceTwo');

        $this->assertSame($outputOne, $outputTwo);
    }

    /**
     * @test
     * it should resolve singleton bindings of different class and interface with same implementation to same callback
     */
    public function it_should_resolve_singleton_bindings_of_different_class_and_interface_with_same_implementation_to_same_callback()
    {
        $container = $this->makeInstance();

        $f = function () {
            return microtime();
        };

        $container->singleton('TestInterfaceOne', $f);
        $container->singleton('ClassOne', $f);

        $outputOne = $container->resolve('TestInterfaceOne');
        $outputTwo = $container->resolve('ClassOne');

        $this->assertSame($outputOne, $outputTwo);
    }

    /**
     * @test
     * it should allow binding a decorator chain using a closure
     */
    public function it_should_allow_binding_a_decorator_chain_using_a_closure()
    {
        $container = $this->makeInstance();

        $container->bind('BaseClassInterface', function ($container) {
            $baseClass = $container->resolve('BaseClass');

            return new BaseClassDecoratorThree(new BaseClassDecoratorTwo(new BaseClassDecoratorOne($baseClass)));
        });

        $instance = $container->resolve('BaseClassInterface');

        $this->assertInstanceOf('BaseClassDecoratorThree', $instance);
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
     * it should allow binding a decorator chain as a singleton using a closure
     */
    public function it_should_allow_binding_a_decorator_chain_as_a_singleton_using_a_closure()
    {
        $container = $this->makeInstance();

        $container->singleton('BaseClassInterface', function ($container) {
            $baseClass = $container->resolve('BaseClass');

            return new BaseClassDecoratorThree(new BaseClassDecoratorTwo(new BaseClassDecoratorOne($baseClass)));
        });

        $instance = $container->resolve('BaseClassInterface');
        $instance2 = $container->resolve('BaseClassInterface');

        $this->assertSame($instance, $instance2);
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
}
