<?php

namespace unit;

use lucatume\DI52\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerExtension extends Container
{
}

class CloneTestFoo
{
    private $baz;

    public function __construct(CloneTestBazInterface $baz)
    {
        $this->baz = $baz;
    }

    public function getBaz()
    {
        return $this->baz;
    }
}

interface CloneTestBazInterface
{
}

class CloneTestBazOne implements CloneTestBazInterface
{

}

class CloneTestBazTwo implements CloneTestBazInterface
{
}

class CloneTest extends TestCase
{
    /**
     * It should support cloning the container
     *
     * @test
     */
    public function should_support_cloning_the_container()
    {
        $container = new Container();
        $object1   = new \stdClass();
        $object2   = new \stdClass();
        $container->bind('bound', $object1);
        $container->singleton('singleton', $object2);

        $clone = clone $container;

        $this->assertNotSame($clone, $container);
        $this->assertTrue($clone->has('bound'));
        $this->assertSame($clone->get('bound'), $container->get('bound'));
        $this->assertTrue($clone->has('singleton'));
        $this->assertSame($clone->get('singleton'), $container->get('singleton'));
        $this->assertFalse($clone->has('not-bound'));
    }

    /**
     * It should clone the container resolver
     *
     * @test
     */
    public function should_clone_the_container_resolver()
    {
        $container = new Container();
        $container->singleton('object', function () {
            return new \stdClass();
        });

        $clone = clone $container;

        $container->setVar('test', 23);
        $clone->setVar('test', 89);

        $this->assertEquals(23, $container->getVar('test'));
        $this->assertEquals(89, $clone->getVar('test'));

        $containerObject = $container->get('object');
        $cloneObject     = $clone->get('object');

        $this->assertSame($container->get('object'), $container->get('object'));
        $this->assertSame($clone->get('object'), $clone->get('object'));
        $this->assertNotSame($containerObject, $cloneObject);
    }

    /**
     * It should bind clone as singleton container on clone
     *
     * @test
     */
    public function should_bind_clone_as_singleton_container_on_clone()
    {
        $container = new Container();
        $clone     = clone $container;

        $this->assertNotSame($container, $clone);
        $this->assertSame($container, $container->get(Container::class));
        $this->assertSame($container, $container->get(ContainerInterface::class));
        $this->assertSame($clone, $clone->get(Container::class));
        $this->assertSame($clone, $clone->get(ContainerInterface::class));
    }

    /**
     * It should bind the clone as singleton when Container class extended
     *
     * @test
     */
    public function should_bind_the_clone_as_singleton_when_container_class_extended()
    {
        $container = new ContainerExtension();
        $clone     = clone $container;

        $this->assertNotSame($container, $clone);
        $this->assertSame($container, $container->get(Container::class));
        $this->assertSame($container, $container->get(ContainerExtension::class));
        $this->assertSame($container, $container->get(ContainerInterface::class));
        $this->assertSame($clone, $clone->get(Container::class));
        $this->assertSame($clone, $clone->get(ContainerExtension::class));
        $this->assertSame($clone, $clone->get(ContainerInterface::class));
    }

    /**
     * It should use the cloned container to build
     *
     * @test
     */
    public function should_use_the_cloned_container_to_build()
    {
        $original = new Container();
        $clone    = clone $original;

        $this->assertNotSame($original, $clone);

        $original->singleton(CloneTestFoo::class);
        $original->singleton(CloneTestBazInterface::class, CloneTestBazOne::class);

        $clone->singleton(CloneTestFoo::class);
        $clone->singleton(CloneTestBazInterface::class, CloneTestBazTwo::class);

        $this->assertNotSame($original->get(CloneTestFoo::class), $clone->get(CloneTestFoo::class));
        $this->assertNotSame(
            $original->get(CloneTestBazInterface::class),
            $clone->get(CloneTestBazInterface::class)
        );
        $this->assertInstanceOf(CloneTestBazOne::class, $original->get(CloneTestBazInterface::class));
        $this->assertInstanceOf(CloneTestBazTwo::class, $clone->get(CloneTestBazInterface::class));
        $this->assertInstanceOf(CloneTestBazOne::class, $original->get(CloneTestFoo::class)->getBaz());
        $this->assertInstanceOf(CloneTestBazTwo::class, $clone->get(CloneTestFoo::class)->getBaz());
        $this->assertNotSame(
            $original->get(CloneTestFoo::class)->getBaz(),
            $clone->get(CloneTestFoo::class)->getBaz()
        );
    }
}
