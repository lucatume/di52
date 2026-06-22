<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;


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

        $this->expectException(ContainerException::class);
        $container->add( 'whatever', ['test2', 'test3']);
    }
}
