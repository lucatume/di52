<?php

class ResolverClosureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var tad_DI52_Container
     */
    protected $container;

    /**
     * @test
     * it should allow binding a callback to an interface
     */
    public function it_should_allow_binding_a_callback_to_an_interface()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

        $resolver = $this->makeInstance();

        $object = (object)array('foo' => 'bar');
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

    private function makeInstance()
    {
        return new tad_DI52_Bindings_Resolver($this->container);
    }

    /**
     * @test
     * it should rerun the callback on each resolution
     */
    public function it_should_rerun_the_callback_on_each_resolution()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

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
     * it should allow binding a singleton callback to an interface
     */
    public function it_should_allow_binding_a_singleton_callback_to_an_interface()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

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
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

        $sut = $this->makeInstance();
        $sut->singleton('ConcreteClassImplementingTestInterfaceOne', function () {
            return microtime();
        });
        $outOne = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');
        $outTwo = $sut->resolve('ConcreteClassImplementingTestInterfaceOne');

        $this->assertSame($outOne, $outTwo);
    }

    /**
     * @test
     * it should resolve singleton bindings of different interfaces with same implementation to same callback
     */
    public function it_should_resolve_singleton_bindings_of_different_interfaces_with_same_implementation_to_same_callback()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

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
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

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
     * it should allow binding callbacks by slug
     */
    public function it_should_allow_binding_callbacks_by_slug()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

        $sut = $this->makeInstance();

        $sut->bind('c.one', function () {
            return new ClassOne();
        });
        $sut->bind('c.base', function () {
            return new BaseClass();
        });

        $this->assertInstanceOf('ClassOne', $sut->resolve('c.one'));
        $this->assertNotSame($sut->resolve('c.one'), $sut->resolve('c.one'));
        $this->assertInstanceOf('BaseClass', $sut->resolve('c.base'));
        $this->assertNotSame($sut->resolve('c.base'), $sut->resolve('c.base'));
    }

    /**
     * @test
     * it should allow binding callbacks as singletons by slug
     */
    public function it_should_allow_binding_callbacks_as_singletons_by_slug()
    {
        if (!version_compare(phpversion(), '5.2.17', '>')) {
            $this->markTestSkipped();
        }

        $sut = $this->makeInstance();

        $sut->singleton('c.one', function () {
            return new ClassOne();
        });
        $sut->singleton('c.base', function () {
            return new BaseClass();
        });

        $this->assertInstanceOf('ClassOne', $sut->resolve('c.one'));
        $this->assertSame($sut->resolve('c.one'), $sut->resolve('c.one'));
        $this->assertInstanceOf('BaseClass', $sut->resolve('c.base'));
        $this->assertSame($sut->resolve('c.base'), $sut->resolve('c.base'));
    }

    /**
     * @test
     * it should allow binding a decorator chain using a closure
     */
    public function it_should_allow_binding_a_decorator_chain_using_a_closure()
    {
        $container = $this->makeInstance();

        $container->bind('BaseClassInterface', function (tad_DI52_Bindings_ResolverInterface $container) {
            $baseClass = $container->resolve('BaseClass');

            return new BaseClassDecoratorThree(new BaseClassDecoratorTwo(new BaseClassDecoratorOne($baseClass)));
        });

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

        $container->singleton('BaseClassInterface', function (tad_DI52_Bindings_ResolverInterface $container) {
            $baseClass = $container->resolve('BaseClass');

            return new BaseClassDecoratorThree(new BaseClassDecoratorTwo(new BaseClassDecoratorOne($baseClass)));
        });

        $instance = $container->resolve('BaseClassInterface');
        $instance2 = $container->resolve('BaseClassInterface');

        $this->assertSame($instance, $instance2);
    }

    protected function setUp()
    {
        $this->container = $this->getMock('tad_DI52_Container');
    }
}
