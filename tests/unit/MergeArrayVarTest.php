<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

class WhateverService {
    /** @var array */
    public $providers;

    public static $spy = 0;

    public function __construct($providers){
        $this->providers = $providers;
        self::$spy++;
    }
}

class MergeArrayVarTest extends TestCase
{
    /**
     * It should allow creating callbacks for instance methods
     *
     * @test
     */
    public function should_simply_bind_when_not_bound()
    {
        $container = new Container();

        $container->mergeArrayVar('whatever', []);

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

        $container->mergeArrayVar('whatever', ['start']);
        $this->assertSame(['start'], $container->get('whatever'));

        $container->mergeArrayVar('whatever', ['middle']);
        $this->assertSame(['start', 'middle'], $container->get('whatever'));

        $container->mergeArrayVar('whatever', ['before', 'the']);
        $this->assertSame(['start', 'middle', 'before', 'the'], $container->get('whatever'));

        $container->mergeArrayVar('whatever', ['end', 1]);
        $this->assertSame(['start', 'middle', 'before', 'the', 'end', 1], $container->get('whatever'));
    }

    /**
     * It should throw a ContainerException when we try to add in a bound non-array value.
     * @test
     */
    public function should_throw_when_base_is_not_an_array()
    {
        $container = new Container();

        $container->mergeArrayVar('whatever', ['test']);

        $this->assertTrue($container->isBound('whatever'));
        $this->assertSame(['test'], $container->get('whatever'));

        $container->bind('whatever', 'test');

        $this->assertSame('test', $container->get('whatever'));
        $container->mergeArrayVar( 'whatever', ['test2', 'test3']);

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

        $container->mergeArrayVar('whatever', ['middle']);
        $this->assertSame(['start', 'middle'], $container->get('whatever'));

        $container->mergeArrayVar('whatever', ['before', 'the']);
        $this->assertSame(['start', 'middle', 'before', 'the'], $container->get('whatever'));

        $container->mergeArrayVar('whatever', ['end', 1]);
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

        $container->mergeArrayVar( 'providers', [] );
        $this->assertTrue($container->isBound('providers'));
        $this->assertSame([], $container->get('providers'));

        $container->mergeArrayVar('providers', ['test', 1, [3, 'test2']]);
        $this->assertSame(['test', 1, [3, 'test2']], $container->get('providers'));

        $container->singleton(WhateverService::class);
        $container->when(WhateverService::class)->needs('$providers')->give(
            static function( $c ) {
                return $c->get('providers');
            }
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

        $container->mergeArrayVar('whatever', [$test]);

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

        $container->mergeArrayVar('whatever', [$test,$test]);

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

        $container->mergeArrayVar('items', [WhateverService::class]);

        $container->mergeArrayVar('items', ['end']);

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
        $container->mergeArrayVar('items', ['end']);

        $container->bind('late.value', 'start');

        $this->assertSame(['start', 'end'], $container->get('items'));
    }

    /**
     * It should not initiate lazy resolved instances.
     *
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

        $container->mergeArrayVar('whatever', static function($c) {
            return [$c->get(WhateverService::class)];
        });

        $container->mergeArrayVar('whatever', ['end']);

        $this->assertSame(0, WhateverService::$spy);

        $value = $container->get('whatever');

        $this->assertSame(1, WhateverService::$spy);
        $this->assertIsArray($value);
        $this->assertCount(2, $value);
        $this->assertSame('end', $value[1]);
        $this->assertInstanceOf(WhateverService::class, $value[0]);
    }

    /**
     * Should allow adding to unresolved singleton.
     *
     * @test
     */
    public function should_add_to_unresolved_singleton_array_binding()
    {
        $container = new Container();

        $container->singleton('items', ['start']);

        $container->mergeArrayVar('items', ['end']);

        $this->assertSame(['start', 'end'], $container->get('items'));
    }

    /**
     * It should not allow adding to a resolved singleton.
     *
     * @test
     */
    public function should_throw_when_adding_to_resolved_singleton_array_binding()
    {
        $container = new Container();

        $container->singleton('items', ['start']);

        $resolved = $container->get('items');

        $this->assertSame(['start'], $resolved);

        $this->expectException(ContainerException::class);

        $container->mergeArrayVar('items', ['end']);
    }

    /**
     * It should not resolve unresolved singleton when adding to it.
     *
     * @test
     */
    public function should_not_resolve_unresolved_singleton_when_adding_values()
    {
        $resolved = 0;

        $container = new Container();

        $container->singleton('items', static function () use (&$resolved): array {
            $resolved++;

            return ['start'];
        });

        $container->mergeArrayVar('items', ['end']);

        $this->assertSame(0, $resolved);
        $this->assertSame(['start', 'end'], $container->get('items'));
        $this->assertSame(1, $resolved);
    }

    /**
     * It should be the same calling get vs offsetGet.
     *
     * @test
     */
    public function should_be_the_same_result_when_calling_offset_get()
    {
        $resolved = 0;

        $container = new Container();

        $container->singleton('items', static function () use (&$resolved): array {
            $resolved++;

            return ['start'];
        });

        $container->mergeArrayVar('items', ['end']);

        $this->assertSame(0, $resolved);
        $this->assertSame(['start', 'end'], $container->offsetGet('items'));
        $this->assertSame(1, $resolved);
    }

    /**
     * It should merge numeric indexed arrays by appending.
     *
     * @test
     */
    public function should_resolve_indexed_numeric_arrays_by_appending()
    {
        $resolved = 0;
        $container = new Container();

        $container->mergeArrayVar('items', static function () use (&$resolved): array {
            $resolved++;

            return [1 => 'test1', 0 => 'test0', 4 => 'test4', 2 => 'test2'];
        });

        $this->assertSame(0, $resolved);

        $container->mergeArrayVar('items', [4 => 'test40', 0 => 'test10', 3 => 'test3']);

        $this->assertSame(0, $resolved);

        $values = $container->get('items');

        $this->assertSame(1, $resolved);

        $this->assertSame(
            [0 => 'test1', 1 => 'test0', 2 => 'test4', 3 => 'test2', 4 => 'test40', 5 => 'test10', 6 => 'test3'],
            $values
        );

        $container->mergeArrayVar('items', static function() use (&$resolved): array {
            $resolved++;

            return [
                7 => 'test7',
                1 => 'test100',
            ];
        });

        $this->assertSame(1, $resolved);

        $new_values = $container->get('items');

        // Resolves twice inside 2 callbacks now.
        $this->assertSame(3, $resolved);

        $this->assertSame(
            [
                0 => 'test1',
                1 => 'test0',
                2 => 'test4',
                3 => 'test2',
                4 => 'test40',
                5 => 'test10',
                6 => 'test3',
                7 => 'test7',
                8 => 'test100',
            ],
            $new_values
        );
    }

    /**
     * It should merge string indexed arrays by overwriting.
     *
     * @test
     */
    public function should_resolve_indexed_string_arrays_by_overwriting()
    {
        $resolved = 0;
        $container = new Container();

        $container->mergeArrayVar('items', static function () use (&$resolved): array {
            $resolved++;

            return ['test1' => 'test1', 'test0' => 'test0', 'test4' => 'test4', 'test2' => 'test2'];
        });

        $this->assertSame(0, $resolved);

        $container->mergeArrayVar('items', ['test4' => 'test40', 'test0' => 'test10', 'test3' => 'test3']);

        $this->assertSame(0, $resolved);

        $values = $container->get('items');

        $this->assertSame(1, $resolved);

        $this->assertSame(
            ['test1' => 'test1', 'test0' => 'test10', 'test4' => 'test40', 'test2' => 'test2', 'test3' => 'test3'],
            $values
        );

        $container->mergeArrayVar('items', static function() use (&$resolved): array {
            $resolved++;

            return [
                'test7' => 'test7',
                'test1' => 'test100',
            ];
        });

        $this->assertSame(1, $resolved);

        $new_values = $container->get('items');

        // Resolves twice inside 2 callbacks now.
        $this->assertSame(3, $resolved);

        $this->assertSame(
            [
                'test1' => 'test100',
                'test0' => 'test10',
                'test4' => 'test40',
                'test2' => 'test2',
                'test3' => 'test3',
                'test7' => 'test7',
            ],
            $new_values
        );
    }

    /**
     * It should merge arrays of numeric and string keys in a deterministic way.
     *
     * @test
     */
    public function should_resolve_indexed_numeric_and_string_arrays()
    {
        $resolved = 0;
        $container = new Container();

        $container->mergeArrayVar('items', static function () use (&$resolved): array {
            $resolved++;

            return [1 => 'test1', 'test0' => 'test0', 'test4' => 'test4', 2 => 'test2'];
        });

        $this->assertSame(0, $resolved);

        $container->mergeArrayVar('items', [4 => 'test40', 'test0' => 'test10', 3 => 'test3']);

        $this->assertSame(0, $resolved);

        $values = $container->get('items');

        $this->assertSame(1, $resolved);

        $this->assertSame(
            [0 => 'test1', 'test0' => 'test10', 'test4' => 'test4', 1 => 'test2', 2 => 'test40', 3 => 'test3'],
            $values
        );

        $container->mergeArrayVar('items', static function() use (&$resolved): array {
            $resolved++;

            return [
                7 => 'test7',
                'test1' => 'test100',
                'test4' => 'test40',
            ];
        });

        $this->assertSame(1, $resolved);

        $new_values = $container->get('items');

        // Resolves twice inside 2 callbacks now.
        $this->assertSame(3, $resolved);

        $this->assertSame(
            [
                0 => 'test1',
                'test0' => 'test10',
                'test4' => 'test40',
                1 => 'test2',
                2 => 'test40',
                3 => 'test3',
                4 => 'test7',
                'test1' => 'test100',
            ],
            $new_values
        );
    }

    /**
     * Should evaluate that the pointer has the binding even when empty array.
     *
     * @test
     */
    public function should_return_it_has_even_when_empty() {
        $container = new Container();

        $this->assertFalse($container->has('items'));

        $container->mergeArrayVar('items', []);

        $this->assertTrue($container->has('items'));
    }
}
