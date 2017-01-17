<?php

class Container53CompatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * it should allow setting a closure var on the container
     */
    public function it_should_allow_setting_a_closure_var_on_the_container()
    {
        $sut = new tad_DI52_Container();

        $closure = function ($value) {
            return $value + 1;
        };

        $sut->setVar('foo', $sut->protect($closure));

        $this->assertEquals($closure, $sut->getVar('foo'));
    }

    /**
     * @test
     * it should allow setting a closure as a variable using the ArrayAccess API
     */
    public function it_should_allow_setting_a_closure_as_a_variable_using_the_array_access_api()
    {
        $sut = new tad_DI52_Container();

        $closure = function ($value) {
            return $value + 1;
        };

        $sut['foo'] = $sut->protect($closure);

        $this->assertEquals($closure, $sut['foo']);
    }

    /**
     * @test
     * it should allow binding a closure as implementation of an interface
     */
    public function it_should_allow_binding_a_closure_as_implementation_of_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', function () {
            return new ClassOne();
        });

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
    }

    /**
     * @test
     * it should pass the container as parameter to the closure implementation
     */
    public function it_should_pass_the_container_as_parameter_to_the_closure_implementation()
    {
        $sut = new tad_DI52_Container();

        $passedContainer = null;

        $sut->bind('One', function ($container) use (&$passedContainer) {
            $passedContainer = $container;
            return new ClassOne();
        });

        $sut->make('One');

        $this->assertSame($sut, $passedContainer);
    }

    /**
     * @test
     * it should allow binding a closure to a string slug
     */
    public function it_should_allow_binding_a_closure_to_a_string_slug()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('foo.bar', function () {
            return 23;
        });

        $this->assertEquals(23, $sut->make('foo.bar'));
    }

    /**
     * @test
     * it should allow binding a closure to an interface as a singletong
     */
    public function it_should_allow_binding_a_closure_to_an_interface_as_a_singletong()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('One', function () {
            return new ClassOne();
        });

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should allow binding a closure to a string slug as a singleton
     */
    public function it_should_allow_binding_a_closure_to_a_string_slug_as_a_singleton()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('foo.one', function () {
            return new ClassOne();
        });

        $this->assertInstanceOf('ClassOne', $sut->make('foo.one'));
        $this->assertSame($sut->make('foo.one'), $sut->make('foo.one'));
    }

    public function namespacedKeysAndValues()
    {
        return array(
            array('Acme\One', 'Acme\ClassOne'),
            array('\Acme\One', '\Acme\ClassOne'),
            array('Acme\One', '\Acme\ClassOne'),
            array('\Acme\One', 'Acme\ClassOne'),
            array('foo.one', '\Acme\ClassOne'),
            array('foo.one', 'Acme\ClassOne'),
        );
    }

    /**
     * @test
     * it should allow binding fully namespaced interfaces and classes with or without leading slash
     * @dataProvider namespacedKeysAndValues
     */
    public function it_should_allow_binding_fully_namespaced_interfaces_and_classes_with_or_without_leading_slash(
        $key,
        $value
    ) {
        $sut = new tad_DI52_Container();

        $sut->bind($key, $value);

        $this->assertInstanceOf('\\' . ltrim($value, '\\'), $sut->make($key));
    }

    /**
     * @test
     * it should allow tagging mixed values
     */
    public function it_should_allow_tagging_mixed_values()
    {
        $sut = new tad_DI52_Container();

        $sut->tag(array(
            'ClassOne',
            new ClassOneOne(),
            function ($container) {
                return $container->make('ClassOneTwo');
            }
        ), 'foo');
        $made = $sut->tagged('foo');

        $this->assertInstanceOf('ClassOne', $made[0]);
        $this->assertInstanceOf('ClassOneOne', $made[1]);
        $this->assertInstanceOf('ClassOneTwo', $made[2]);
    }

    /**
     * @test
     * it should allow contextual binding of closures
     */
    public function it_should_allow_contextual_binding_of_closures()
    {
        $sut = new tad_DI52_Container();

        $sut->when('ClassSixOne')
            ->needs('ClassOne')
            ->give(function ($container) {
                return $container->make('ExtendingClassOneOne');
            });

        $sut->when('ClassSevenOne')
            ->needs('ClassOne')
            ->give(function ($container) {
                return $container->make('ExtendingClassOneTwo');
            });

        $this->assertInstanceOf('ClassOne', $sut->make('ClassOne'));
        $this->assertInstanceOf('ExtendingClassOneOne', $sut->make('ClassSixOne')->getOne());
        $this->assertInstanceOf('ExtendingClassOneTwo', $sut->make('ClassSevenOne')->getOne());
    }

    /**
     * @test
     * it should call a closure when bound to an offset in ArrayAccess API
     */
    public function it_should_call_a_closure_when_bound_to_an_offset_in_array_access_api()
    {
        $sut = new tad_DI52_Container();

        $sut['foo'] = function () {
            return 'bar';
        };

        $this->assertEquals('bar', $sut['foo']);
    }

    /**
     * @test
     * it should replace a binding when re-binding
     */
    public function it_should_replace_a_binding_when_re_binding()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', function ($container) {
            return $container->make('ClassOne');
        });

        $this->assertInstanceOf('ClassOne', $sut->make('One'));

        $sut->bind('One', function ($container) {
            return $container->make('ClassOneOne');
        });

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
    }

    /**
     * @test
     * it should replace a singleton bind when re-binding a singleton binding
     */
    public function it_should_replace_a_singleton_bind_when_re_binding_a_singleton_binding()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('One', function ($container) {
            return $container->make('ClassOne');
        });

        $this->assertInstanceOf('ClassOne', $sut->make('One'));

        $sut->bind('One', function ($container) {
            return $container->make('ClassOneOne');
        });

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertNotSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should replace bind with singleton if re-binding as singleton
     */
    public function it_should_replace_bind_with_singleton_if_re_binding_as_singleton()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('One', function ($container) {
            return $container->make('ClassOne');
        });

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));

        $sut->singleton('One', function ($container) {
            return $container->make('ClassOneOne');
        });

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should replace singleton with simple bind if re-binding as non singleton
     */
    public function it_should_replace_singleton_with_simple_bind_if_re_binding_as_non_singleton()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('One', function ($container) {
            return $container->make('ClassOne');
        });

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));

        $sut->bind('One', function ($container) {
            return $container->make('ClassOneOne');
        });

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertNotSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should allow to lazy make a closure
     */
    public function it_should_allow_to_lazy_make_a_closure()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('foo', function ($container) {
            return $container->make('FourBase');
        });

        $f = $sut->callback('foo', 'methodThree');

        $this->assertEquals(28, $f(5));
    }

    /**
     * @test
     * it should resolve bound closures in instance method
     */
    public function it_should_resolve_bound_closures_in_instance_method()
    {
        ClassTwelve::reset();

        $sut = new tad_DI52_Container();

        $one = function ($container) {
            return $container->make('ClassOne');
        };

        $sut->bind('One', $one);

        $f = $sut->instance('ClassTwelve', array('One'));

        $this->assertInstanceOf('ClassOne', $f()->getVarOne());
    }

    /**
     * @test
     * it should resolve singleton closures in instance method
     */
    public function it_should_resolve_singleton_closures_in_instance_method()
    {
        ClassTwelve::reset();

        $sut = new tad_DI52_Container();

        $one = function ($container) {
            return $container->make('ClassOne');
        };

        $sut->singleton('One', $one);

        $f = $sut->instance('ClassTwelve', array('One'));

        $this->assertInstanceOf('ClassOne', $f()->getVarOne());
        $this->assertSame($f()->getVarOne(), $f()->getVarOne());
    }

    /**
     * @test
     * it should resolve slug bound closures in instance method
     */
    public function it_should_resolve_slug_bound_closures_in_instance_method()
    {
        ClassTwelve::reset();

        $sut = new tad_DI52_Container();

        $one = function ($container) {
            return $container->make('ClassOne');
        };

        $sut->bind('foo', $one);

        $f = $sut->instance('ClassTwelve', array('foo'));

        $this->assertInstanceOf('ClassOne', $f()->getVarOne());
        $this->assertNotSame($f()->getVarOne(), $f()->getVarOne());
    }

    /**
     * @test
     * it should resolve singleton slug bound closures in instance method
     */
    public function it_should_resolve_singleton_slug_bound_closures_in_instance_method()
    {
        ClassTwelve::reset();

        $sut = new tad_DI52_Container();

        $one = function ($container) {
            return $container->make('ClassOne');
        };

        $sut->singleton('foo', $one);

        $f = $sut->instance('ClassTwelve', array('foo'));

        $this->assertInstanceOf('ClassOne', $f()->getVarOne());
        $this->assertSame($f()->getVarOne(), $f()->getVarOne());
    }

    /**
     * @test
     * it should allow binding and getting an object built as a closure
     */
    public function it_should_allow_binding_and_getting_an_object_built_as_a_closure()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', function ($container) {
            return $container->make('ClassOne');
        });

        $this->assertInstanceOf('ClassOne', $sut['One']);
        $this->assertNotSame($sut['One'], $sut['One']);
    }
}
