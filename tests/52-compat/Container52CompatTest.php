<?php

class Container52CompatTest extends PHPUnit_Framework_TestCase
{

    public function boundVariables()
    {
        return array(
            array('bar'),
            array(23),
            array((object)array('prop' => 'value')),
            array(''),
        );
    }

    /**
     * @test
     * it should allow setting a var on the container
     * @dataProvider boundVariables
     */
    public function it_should_allow_setting_a_var_on_the_container($value)
    {
        $sut = new tad_DI52_Container();

        $sut->setVar('foo', $value);

        $this->assertEquals($value, $sut->getVar('foo'));
    }

    /**
     * @test
     * it should allow setting a var on the container with ArrayAccess API
     * @dataProvider boundVariables
     */
    public function it_should_allow_setting_a_var_on_the_container_with_array_access_api($value)
    {
        $sut = new tad_DI52_Container();

        $sut['foo'] = $value;

        $this->assertEquals($value, $sut['foo']);
    }

    /**
     * @test
     * it should allow binding an implementation with no constructor to an interface
     */
    public function it_should_allow_binding_an_implementation_with_no_constructor_to_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
    }

    /**
     * @test
     * it should return a different instance of a bound interface implementation on each build
     */
    public function it_should_return_a_different_instance_of_a_bound_interface_implementation_on_each_build()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');

        $this->assertNotSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should allow binding an implementation with constructor to an interface
     */
    public function it_should_allow_binding_an_implementation_with_constructor_to_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
    }

    /**
     * @test
     * it should allow binding an implementation with constructor arguments
     */
    public function it_should_allow_binding_an_implementation_with_constructor_arguments()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOneTwo');

        $this->assertInstanceOf('ClassOneTwo', $sut->make('One'));
    }

    /**
     * @test
     * it should allow binding an implementation to a string slug
     */
    public function it_should_allow_binding_an_implementation_to_a_string_slug()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('foo', 'ClassOneTwo');

        $this->assertInstanceOf('ClassOneTwo', $sut->make('foo'));
    }

    /**
     * @test
     * it should allow binding an implementation to a class
     */
    public function it_should_allow_binding_an_implementation_to_a_class()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('ClassOne', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('ClassOne'));
    }

    /**
     * @test
     * it should resolve unbound class with no constructor
     */
    public function it_should_resolve_unbound_class_with_no_constructor()
    {
        $sut = new tad_DI52_Container();

        $this->assertInstanceOf('ClassOne', $sut->make('ClassOne'));
    }

    /**
     * @test
     * it should resolve an unbound class with a defaulted scalar dependency
     */
    public function it_should_resolve_an_unbound_class_with_a_defaulted_scalar_dependency()
    {
        $sut = new tad_DI52_Container();

        $this->assertInstanceOf('ClassOneTwo', $sut->make('ClassOneTwo'));
    }

    /**
     * @test
     * it should throw if trying to resolve class with unbound interface dependency
     */
    public function it_should_throw_if_trying_to_resolve_class_with_unbound_interface_dependency()
    {
        $this->setExpectedException('RuntimeException');

        $sut = new tad_DI52_Container();

        $sut->make('ClassTwo');
    }

    /**
     * @test
     * it should resolve an unbound class with an interface dependency
     */
    public function it_should_resolve_an_unbound_class_with_an_interface_dependency()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');

        $this->assertInstanceOf('ClassTwo', $sut->make('ClassTwo'));
    }

    /**
     * @test
     * it should resolve an unbound class with a class dependency
     */
    public function it_should_resolve_an_unbound_class_with_a_class_dependency()
    {
        $sut = new tad_DI52_Container();

        $this->assertInstanceOf('ClassTwoOne', $sut->make('ClassTwoOne'));
    }

    /**
     * @test
     * it should resolve an unbound class with plus one interface dependencies
     */
    public function it_should_resolve_an_unbound_class_with_plus_one_interface_dependencies()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');
        $sut->bind('Two', 'ClassTwo');

        $this->assertInstanceOf('ClassThree', $sut->make('ClassThree'));
    }

    /**
     * @test
     * it should resolve an unbound class with plus one class dependencies
     */
    public function it_should_resolve_an_unbound_class_with_plus_one_class_dependencies()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');

        $this->assertInstanceOf('ClassThreeOne', $sut->make('ClassThreeone'));
    }

    /**
     * @test
     * it should resolve an unbound class with plus one interface and class dependencies
     */
    public function it_should_resolve_an_unbound_class_with_plus_one_interface_and_class_dependencies()
    {
        $sut = new tad_DI52_Container();

        $this->assertInstanceOf('ClassThreeTwo', $sut->make('ClassThreeTwo'));
    }

    /**
     * @test
     * it should throw if trying to resolve class with non type-hinted dependency without default
     */
    public function it_should_throw_if_trying_to_resolve_class_with_non_type_hinted_dependency_without_default()
    {
        $sut = new tad_DI52_Container();

        $this->setExpectedException('RuntimeException');

        $sut->make('ClassFour');
    }

    /**
     * @test
     * it should allow binding a class to an interface as a singleton
     */
    public function it_should_allow_binding_a_class_to_an_interface_as_a_singleton()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('One', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should allow binding a class to a class as a singleton
     */
    public function it_should_allow_binding_a_class_to_a_class_as_a_singleton()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('ClassOne', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('ClassOne'));
        $this->assertSame($sut->make('ClassOne'), $sut->make('ClassOne'));
    }

    /**
     * @test
     * it should allow binding a class to a string slug as a singleton
     */
    public function it_should_allow_binding_a_class_to_a_string_slug_as_a_singleton()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('one.foo', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('one.foo'));
        $this->assertSame($sut->make('one.foo'), $sut->make('one.foo'));
    }

    public function implementationKeysAndValues()
    {
        return array(
            array('One', 'ClassOne'),
            array('ClassOne', 'ClassOneOne'),
        );
    }

    /**
     * @test
     * it should bind an implementation as singleton when using ArrayAccess API
     * @dataProvider implementationKeysAndValues
     */
    public function it_should_bind_an_implementation_as_singleton_when_using_array_access_api($key, $value)
    {
        $sut = new tad_DI52_Container();

        $sut[$key] = $value;

        $this->assertInstanceOf($value, $sut[$key]);
        $this->assertSame($sut[$key], $sut[$key]);
    }

    /**
     * @test
     * it should allow binding a decorator chain to an interface
     */
    public function it_should_allow_binding_a_decorator_chain_to_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->bindDecorators('Four', array('FourDecoratorOne', 'FourDecoratorTwo', 'FourDecoratorThree', 'FourBase'));

        $this->assertInstanceOf('Four', $sut->make('Four'));
        $this->assertNotSame($sut->make('Four'), $sut->make('Four'));
    }

    /**
     * @test
     * it should allow binding a chain as a singleton to an interface
     */
    public function it_should_allow_binding_a_chain_as_a_singleton_to_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->singletonDecorators('Four',
            array('FourDecoratorOne', 'FourDecoratorTwo', 'FourDecoratorThree', 'FourBase'));

        $this->assertInstanceOf('Four', $sut->make('Four'));
        $this->assertSame($sut->make('Four'), $sut->make('Four'));
    }

    /**
     * @test
     * it should allow binding a complex chain to an interface
     */
    public function it_should_allow_binding_a_complex_chain_to_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('Four', 'FourTwo');
        $sut->bind('One', 'ClassOne');
        $sut->bind('Two', 'ClassTwo');

        $sut->bindDecorators('Five', array('FiveDecoratorOne', 'FiveDecoratorTwo', 'FiveDecoratorThree', 'FiveBase'));

        $this->assertInstanceOf('Five', $sut->make('Five'));
        $this->assertNotSame($sut->make('Five'), $sut->make('Five'));
    }

    /**
     * @test
     * it should allow binding a complex chain as a singleton to an interface
     */
    public function it_should_allow_binding_a_complex_chain_as_a_singleton_to_an_interface()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('Four', 'FourTwo');
        $sut->bind('One', 'ClassOne');
        $sut->bind('Two', 'ClassTwo');

        $sut->singletonDecorators('Five',
            array('FiveDecoratorOne', 'FiveDecoratorTwo', 'FiveDecoratorThree', 'FiveBase'));

        $this->assertInstanceOf('Five', $sut->make('Five'));
        $this->assertSame($sut->make('Five'), $sut->make('Five'));
    }

    /**
     * @test
     * it should allow tagging and resolving tagged classes
     */
    public function it_should_allow_tagging_and_resolving_tagged_classes()
    {
        $sut = new tad_DI52_Container();

        $sut->tag(array('ClassOne', 'ClassOneOne', 'ClassOneTwo'), 'foo');
        $made = $sut->tagged('foo');

        $this->assertInstanceOf('ClassOne', $made[0]);
        $this->assertInstanceOf('ClassOneOne', $made[1]);
        $this->assertInstanceOf('ClassOneTwo', $made[2]);
    }

    /**
     * @test
     * it should throw if trying to resolve non existing tag
     */
    public function it_should_throw_if_trying_to_resolve_non_existing_tag()
    {
        $sut = new tad_DI52_Container();

        $this->setExpectedException('RuntimeException');

        $sut->tagged('foo');
    }

    /**
     * @test
     * it should allow tagging mixed values
     */
    public function it_should_allow_tagging_mixed_values()
    {
        $sut = new tad_DI52_Container();

        $sut->tag(array('ClassOne', new ClassOneOne(), 'ClassOneTwo'), 'foo');
        $made = $sut->tagged('foo');

        $this->assertInstanceOf('ClassOne', $made[0]);
        $this->assertInstanceOf('ClassOneOne', $made[1]);
        $this->assertInstanceOf('ClassOneTwo', $made[2]);
    }

    /**
     * @test
     * it should call register method on on deferred service providers when registering
     */
    public function it_should_call_register_method_on_on_deferred_service_providers_when_registering()
    {
        $sut = new tad_DI52_Container();

        $sut->register('ProviderOne');

        $this->assertTrue($sut->isBound('foo'));
    }

    /**
     * @test
     * it should not call register method on deferred provider on registration
     */
    public function it_should_not_call_register_method_on_deferred_provider_on_registration()
    {
        $sut = new tad_DI52_Container();

        $sut->register('DeferredProviderTwo');

        $this->assertFalse($sut->isBound('One'));
    }

    /**
     * @test
     * it should throw if deferred provider is not providing anything
     */
    public function it_should_throw_if_deferred_provider_is_not_providing_anything()
    {
        $sut = new tad_DI52_Container();

        $this->setExpectedException('RuntimeException');

        $sut->register('DeferredProviderOne');
    }

    /**
     * @test
     * it should register deferred provider when trying to resolve provided class
     */
    public function it_should_register_deferred_provider_when_trying_to_resolve_provided_class()
    {
        $sut = new tad_DI52_Container();

        $sut->register('DeferredProviderTwo');

        $this->assertFalse($sut->isBound('One'));

        $sut->make('One');

        $this->assertTrue($sut->isBound('One'));
    }

    /**
     * @test
     * it should call boot method on providers when booting the container
     */
    public function it_should_call_boot_method_on_providers_when_booting_the_container()
    {
        $sut = new tad_DI52_Container();

        $sut->register('ProviderThree');

        $this->assertFalse($sut->isBound('One'));

        $sut->boot();

        $this->assertTrue($sut->isBound('One'));
    }

    /**
     * @test
     * it should call after build methods on implementations
     */
    public function it_should_call_after_build_methods_on_implementations()
    {
        $sut = new tad_DI52_Container();

        $mock = $this->getMock('ClassOneThree');
        $mock->expects($this->once())->method('methodOne');
        $mock->expects($this->once())->method('methodTwo');

        $sut->bind('One', $mock, array('methodOne', 'methodTwo'));

        $sut->make('One');
    }

    /**
     * @test
     * it should bind an object implementation as a singleton even when using bind
     */
    public function it_should_bind_an_object_implementation_as_a_singleton_even_when_using_bind()
    {
        $sut = new tad_DI52_Container();

        $object = new stdClass();

        $sut->bind('foo', $object);

        $this->assertInstanceOf('stdClass', $sut->make('foo'));
        $this->assertSame($sut->make('foo'), $sut->make('foo'));
    }

    /**
     * @test
     * it should support contextual binding of interfaces
     */
    public function it_should_support_contextual_binding_of_interfaces()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');

        $sut->when('ClassSix')
            ->needs('One')
            ->give('ClassOneOne');

        $sut->when('ClassSeven')
            ->needs('One')
            ->give('ClassOneTwo');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertInstanceOf('ClassOneOne', $sut->make('ClassSix')->getOne());
        $this->assertInstanceOf('ClassOneTwo', $sut->make('ClassSeven')->getOne());
    }

    /**
     * @test
     * it should support contextual binding of classes
     */
    public function it_should_support_contextual_binding_of_classes()
    {
        $sut = new tad_DI52_Container();

        $sut->when('ClassSixOne')
            ->needs('ClassOne')
            ->give('ExtendingClassOneOne');

        $sut->when('ClassSevenOne')
            ->needs('ClassOne')
            ->give('ExtendingClassOneTwo');

        $this->assertInstanceOf('ClassOne', $sut->make('ClassOne'));
        $this->assertInstanceOf('ExtendingClassOneOne', $sut->make('ClassSixOne')->getOne());
        $this->assertInstanceOf('ExtendingClassOneTwo', $sut->make('ClassSevenOne')->getOne());
    }

    /**
     * @test
     * it should throw when trying to make non existing class
     */
    public function it_should_throw_when_trying_to_make_non_existing_class()
    {
        $sut = new tad_DI52_Container();

        $this->setExpectedException('RuntimeException');

        $sut->make('SomeNonExistingClass');
    }

    /**
     * @test
     * it should allow binding same implementation to multiple aliases
     */
    public function it_should_allow_binding_same_implementation_to_multiple_aliases()
    {
        $sut = new tad_DI52_Container();

        $sut->bind(array('foo', 'One'), 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('foo'));
        $this->assertInstanceOf('ClassOne', $sut->make('One'));
    }

    /**
     * @test
     * it should allow binding same singleton implementation to multiple aliases
     */
    public function it_should_allow_binding_same_singleton_implementation_to_multiple_aliases()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton(array('foo', 'One'), 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('foo'));
        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('foo'), $sut->make('One'));
    }

    /**
     * @test
     * it should replace a binding when re-binding
     */
    public function it_should_replace_a_binding_when_re_binding()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('One', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));

        $sut->bind('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
    }

    /**
     * @test
     * it should replace a singleton bind when re-binding a singleton binding
     */
    public function it_should_replace_a_singleton_bind_when_re_binding_a_singleton_binding()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton('One', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));

        $sut->bind('One', 'ClassOneOne');

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

        $sut->singleton('One', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));

        $sut->singleton('One', 'ClassOneOne');

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

        $sut->singleton('One', 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));

        $sut->bind('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertNotSame($sut->make('One'), $sut->make('One'));
    }

    /**
     * @test
     * it should replace a binding when re-binding with multiple aliases with multiple aliases
     */
    public function it_should_replace_a_binding_when_re_binding_with_multiple_aliases()
    {
        $sut = new tad_DI52_Container();

        $sut->bind(array('One', 'foo'), 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));

        $sut->bind('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertInstanceOf('ClassOne', $sut->make('foo'));
    }

    /**
     * @test
     * it should replace a singleton bind when re-binding a singleton binding with multiple aliases
     */
    public function it_should_replace_a_singleton_bind_when_re_binding_a_singleton_binding_with_multiple_aliases()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton(array('One', 'foo'), 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));

        $sut->bind('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertInstanceOf('ClassOne', $sut->make('foo'));
        $this->assertNotSame($sut->make('One'), $sut->make('One'));
        $this->assertSame($sut->make('foo'), $sut->make('foo'));
    }

    /**
     * @test
     * it should replace bind with singleton if re-binding as singleton with multiple aliases
     */
    public function it_should_replace_bind_with_singleton_if_re_binding_as_singleton_with_multiple_aliases()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton(array('One', 'foo'), 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));

        $sut->singleton('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));
        $this->assertInstanceOf('ClassOne', $sut->make('foo'));
        $this->assertSame($sut->make('foo'), $sut->make('foo'));
    }

    /**
     * @test
     * it should replace singleton with simple bind if re-binding as non singleton with multiple aliases
     */
    public function it_should_replace_singleton_with_simple_bind_if_re_binding_as_non_singleton_with_multiple_aliases()
    {
        $sut = new tad_DI52_Container();

        $sut->singleton(array('One', 'foo'), 'ClassOne');

        $this->assertInstanceOf('ClassOne', $sut->make('One'));
        $this->assertSame($sut->make('One'), $sut->make('One'));

        $sut->bind('One', 'ClassOneOne');

        $this->assertInstanceOf('ClassOneOne', $sut->make('One'));
        $this->assertNotSame($sut->make('One'), $sut->make('One'));
        $this->assertInstanceOf('ClassOne', $sut->make('foo'));
        $this->assertSame($sut->make('foo'), $sut->make('foo'));
    }

    /**
     * @test
     * it should allow tagging non concrete implementations
     */
    public function it_should_allow_tagging_non_concrete_implementations()
    {
        $sut = new tad_DI52_Container();

        $sut->bind('foo', 'ClassOne');
        $sut->bind('One', 'ClassOne');

        $sut->tag(array('foo', 'One'), 'bar');

        $resolved = $sut->tagged('bar');

        $this->assertCount(2, $resolved);
        $this->assertInstanceOf('ClassOne', $resolved[0]);
        $this->assertInstanceOf('ClassOne', $resolved[1]);
        $this->assertNotSame($resolved[0], $resolved[1]);
    }
}
