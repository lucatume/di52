<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;

interface MessageInterface
{
}

class Message implements MessageInterface
{
}

class PrivateMessage implements MessageInterface
{
}

class EncryptedMessage implements MessageInterface
{
}

interface CacheInterface
{
}

class Cache implements CacheInterface
{
}

class ExternalCache implements CacheInterface
{
}

class DbCache implements CacheInterface
{
}

class NullCache implements CacheInterface
{
}

class DecoratorTest extends TestCase
{
    /**
     * It should throw if trying to bind empty decorator chain
     *
     * @test
     */
    public function should_throw_if_trying_to_bind_empty_decorator_chain()
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        $container->bindDecorators('test', []);
    }

    /**
     * It should allow binding a decorator chain with base only
     *
     * @test
     */
    public function should_allow_binding_a_decorator_chain_with_base_only()
    {
        $container = new Container();

        $container->bindDecorators(Message::class, [ Message::class ]);

        $this->assertInstanceOf(Message::class, $container->make(Message::class));
    }

    /**
     * It should allow binding a decorator chain
     *
     * @test
     */
    public function should_allow_binding_a_decorator_chain()
    {
        $container = new Container();

        $container->bindDecorators(Message::class, [
            EncryptedMessage::class,
            PrivateMessage::class,
            Message::class
        ]);

        $this->assertInstanceOf(EncryptedMessage::class, $container->make(Message::class));
        $this->assertInstanceOf(MessageInterface::class, $container->make(Message::class));
    }

    /**
     * It should allow binding a decorator chain as singleton
     *
     * @test
     */
    public function should_allow_binding_a_decorator_chain_as_singleton()
    {
        $container = new Container();

        $container->singletonDecorators(CacheInterface::class, [
            ExternalCache::class,
            DbCache::class,
            Cache::class
        ]);

        $this->assertInstanceOf(CacheInterface::class, $container->make(CacheInterface::class));
        $this->assertInstanceOf(ExternalCache::class, $container->make(CacheInterface::class));
        $this->assertSame($container->make(CacheInterface::class), $container->make(CacheInterface::class));
    }

    /**
     * It should allow calling after build methods on all decorators
     *
     * @test
     */
    public function should_allow_calling_after_build_methods_on_all_decorators()
    {
        require_once(__DIR__ . '/data/AfterBuildDecoratorClasses.php');
        AfterBuildDecoratorThree::reset();
        AfterBuildDecoratorTwo::reset();
        AfterBuildDecoratorOne::reset();
        AfterBuildBase::reset();

        $container = new Container();

        $container->bindDecorators(
            ZorpMaker::class,
            [
                AfterBuildDecoratorThree::class,
                AfterBuildDecoratorTwo::class,
                AfterBuildDecoratorOne::class,
                AfterBuildBase::class
            ],
            [ 'setupTheZorps' ],
            true
        );

        $zorpMaker = $container->get(ZorpMaker::class);

        $this->assertTrue(AfterBuildDecoratorOne::$didSetUpTheZorps);
        $this->assertTrue(AfterBuildDecoratorTwo::$didSetUpTheZorps);
        $this->assertTrue(AfterBuildDecoratorThree::$didSetUpTheZorps);
        $this->assertTrue(AfterBuildBase::$didSetUpTheZorps);
        $this->assertInstanceOf(AfterBuildDecoratorThree::class, $zorpMaker);
        $this->assertEquals('3 - 2 - 1 - base', $zorpMaker->makeZorps());
    }

    /**
     * It should only call afterBuild method on base instance of decorator chain by default
     *
     * @test
     */
    public function should_only_call_after_build_method_on_base_instance_of_decorator_chain_by_default()
    {
        require_once(__DIR__ . '/data/AfterBuildDecoratorClasses.php');
        AfterBuildDecoratorThree::reset();
        AfterBuildDecoratorTwo::reset();
        AfterBuildDecoratorOne::reset();
        AfterBuildBase::reset();

        $container = new Container();

        $container->bindDecorators(
            ZorpMaker::class,
            [
                AfterBuildDecoratorThree::class,
                AfterBuildDecoratorTwo::class,
                AfterBuildDecoratorOne::class,
                AfterBuildBase::class
            ],
            [ 'setupTheZorps' ]
        );

        $zorpMaker = $container->get(ZorpMaker::class);

        $this->assertFalse(AfterBuildDecoratorOne::$didSetUpTheZorps);
        $this->assertFalse(AfterBuildDecoratorTwo::$didSetUpTheZorps);
        $this->assertFalse(AfterBuildDecoratorThree::$didSetUpTheZorps);
        $this->assertTrue(AfterBuildBase::$didSetUpTheZorps);
        $this->assertInstanceOf(AfterBuildDecoratorThree::class, $zorpMaker);
        $this->assertEquals('3 - 2 - 1 - base', $zorpMaker->makeZorps());
    }
}
