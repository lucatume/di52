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
        $container = new Container() ;

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
        $container = new Container() ;

        $container->bindDecorators(Message::class, [Message::class]);

        $this->assertInstanceOf(Message::class, $container->make(Message::class));
    }

    /**
     * It should allow binding a decorator chain
     *
     * @test
     */
    public function should_allow_binding_a_decorator_chain()
    {
        $container = new Container() ;

        $container->bindDecorators(Message::class, [EncryptedMessage::class,PrivateMessage::class,Message::class]);

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
        $container = new Container() ;

        $container->singletonDecorators(CacheInterface::class, [ExternalCache::class,DbCache::class,Cache::class]);

        $this->assertInstanceOf(CacheInterface::class, $container->make(CacheInterface::class));
        $this->assertInstanceOf(ExternalCache::class, $container->make(CacheInterface::class));
        $this->assertSame($container->make(CacheInterface::class), $container->make(CacheInterface::class));
    }
}
