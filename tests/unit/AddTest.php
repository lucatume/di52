<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class WhateverService {
    /** @var array */
    public $providers;

    public static int $spy = 0;

    public function __construct($providers){
        $this->providers = $providers;
        self::$spy++;
    }
}

class AddTest extends TestCase
{
    /**
     * It should allow creating callbacks for instance methods
     *
     * @test
     */
    public function should_simply_bind_when_not_bound()
    {
        $container = new Container();

        $container->add('whatever', []);

        $this->assertTrue($container->isBound('whatever'));
        $this->assertSame([], $container->get('whatever'));
    }

    /**
     * It should merge definitions.
     *
     * @test
     */
    public function should_add_to_existing_binding()
    {
        $container = new Container();

        $container->add('whatever', ['start']);
        $this->assertSame(['start'], $container->get('whatever'));

        $container->add('whatever', ['middle']);
        $this->assertSame(['start', 'middle'], $container->get('whatever'));

        $container->add('whatever', ['before', 'the']);
        $this->assertSame(['start', 'middle', 'before', 'the'], $container->get('whatever'));

        $container->add('whatever', ['end', 1]);
        $this->assertSame(['start', 'middle', 'before', 'the', 'end', 1], $container->get('whatever'));
    }

    /**
     * It should throw a ContainerException when we try to add in a bound non-array value.
     * @test
     */
    public function should_throw_when_base_is_not_an_array()
    {
        $container = new Container();

        $container->add('whatever', ['test']);

        $this->assertTrue($container->isBound('whatever'));
        $this->assertSame(['test'], $container->get('whatever'));

        $container->bind('whatever', 'test');

        $this->assertSame('test', $container->get('whatever'));
        $container->add( 'whatever', ['test2', 'test3']);

        $this->expectException(ContainerException::class);
        $container->get('whatever');
    }

    /**
     * It should treat initial bind as an add equivalent.
     *
     * @test
     */
    public function should_treat_initial_bind_as_an_add()
    {
        $container = new Container();

        $container->bind('whatever', ['start']);
        $this->assertSame(['start'], $container->get('whatever'));

        $container->add('whatever', ['middle']);
        $this->assertSame(['start', 'middle'], $container->get('whatever'));

        $container->add('whatever', ['before', 'the']);
        $this->assertSame(['start', 'middle', 'before', 'the'], $container->get('whatever'));

        $container->add('whatever', ['end', 1]);
        $this->assertSame(['start', 'middle', 'before', 'the', 'end', 1], $container->get('whatever'));
    }


    /**
     * Add definitions get correctly resolved through when(), needs() & give()
     *
     * @test
     */
    public function should_be_resolvable_through_when_needs_give()
    {
        $container = new Container();

        $container->add( 'providers', [] );
        $this->assertTrue($container->isBound('providers'));
        $this->assertSame([], $container->get('providers'));

        $container->add('providers', ['test', 1, [3, 'test2']]);
        $this->assertSame(['test', 1, [3, 'test2']], $container->get('providers'));

        $container->singleton(WhateverService::class);
        $container->when(WhateverService::class)->needs('$providers')->give(
            static fn( $c ) => $c->get('providers')
        );

        $whatever = $container->get(WhateverService::class);

        $this->assertSame(['test', 1, [3, 'test2']], $whatever->providers);
    }

    /**
     * It should not execute callbacks
     *
     * @test
     */
    public function should_not_execute_callbacks()
    {
        $spy = 0;
        $test = static function() use (&$spy) {
            $spy++;
        };

        $container = new Container();

        $container->add('whatever', [$test]);

        $this->assertTrue($container->isBound('whatever'));
        $value = $container->get('whatever');
        $this->assertSame(0, $spy);
        $this->assertIsArray($value);
        array_values($value)[0]();
        $this->assertSame(1, $spy);
    }

    /**
     * It should execute callbacks bound directly via bind
     *
     * @test
     */
    public function should_execute_callbacks_added_via_bind_but_not_via_add()
    {
        $spy = 0;
        $test = static function() use (&$spy) {
            $spy++;
            return ['test'];
        };

        $container = new Container();

        $container->bind('whatever', $test);

        $this->assertTrue($container->isBound('whatever'));

        $container->add('whatever', [$test,$test]);

        $value = $container->get('whatever');
        $this->assertSame(1, $spy);
        $this->assertIsArray($value);

        $this->assertSame(['test', $test, $test], $value);
    }

    /**
     * It should not resolve existing lazy bindings by adding bindings.
     *
     * @test
     */
    public function should_not_resolve_existing_binding_when_adding_bindings()
    {
        // Dont allow other tests to leak here.
        WhateverService::$spy = 0;
        $container = new Container();

        // Factory binding - so every new get should result in a new instance
        $container->bind(WhateverService::class);

        $container->add('items', [WhateverService::class]);

        $container->add('items', ['end']);

        $value = $container->get('items');

        $container->when(WhateverService::class)->needs('$providers')->give( [] );

        $this->assertSame([WhateverService::class, 'end'], $container->get('items'));
        $this->assertSame(0, WhateverService::$spy);

        $container->get(WhateverService::class);
        $this->assertSame(1, WhateverService::$spy);

        $container->get(WhateverService::class);
        $this->assertSame(2, WhateverService::$spy);
    }

    /**
     * It should not resolve existing lazy bindings when adding.
     *
     * @test
     */
    public function should_not_resolve_existing_lazy_binding_when_adding_values()
    {
        $container = new Container();

        $container->bind('items', static function (Container $container): array {
            return [
                $container->get('late.value'),
            ];
        });

        // This should only register the additional value.
        $container->add('items', ['end']);

        $container->bind('late.value', 'start');

        $this->assertSame(['start', 'end'], $container->get('items'));
    }

    /**
     * @test
     */
    public function should_not_initiate_lazy_resolved_instances()
    {
        // Dont allow other tests to leak here.
        WhateverService::$spy = 0;
        $container = new Container();

        // Factory binding - so every new get should result in a new instance
        $container->bind(WhateverService::class);
        $container->when(WhateverService::class)->needs('$providers')->give( [] );

        $container->add('whatever', static fn($c) => [$c->get(WhateverService::class)] );

        $container->add('whatever', ['end']);

        $this->assertSame(0, WhateverService::$spy);

        $value = $container->get('whatever');

        $this->assertSame(1, WhateverService::$spy);
        $this->assertIsArray($value);
        $this->assertCount(2, $value);
        $this->assertSame('end', $value[1]);
        $this->assertInstanceOf(WhateverService::class, $value[0]);
    }
}
