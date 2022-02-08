<?php
use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use lucatume\DI52\NotFoundException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/data/DependingOnFatalError.php';

class FatalErrorHandlingTest extends TestCase
{
    /**
     * @beforeClass
     */
    public static function before_all()
    {
        spl_autoload_register(static function ($class) {
            if (strpos($class, 'FatalErrorClass') === 0) {
                require_once __DIR__ . "/data/{$class}.php";
            }
        });
    }

    /**
     * @before
     */
    protected function before_each()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped('Fatal error handling is only available on PHP 7.0+');
        }
    }

    /**
     * It should handle a PHP Fatal error
     *
     * @test
     */
    public function should_handle_a_php_fatal_error()
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        $container->get(FatalErrorClass::class);
    }

    /**
     * It should correctly format a fatal error
     *
     * @test
     */
    public function should_correctly_format_a_fatal_error()
    {
        $container = new Container();

        try {
            $container->get(FatalErrorClassTwo::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(ContainerException::class, $e);
            $this->assertNotInstanceOf(NotFoundException::class, $e);
            assertMatchesSnapshots($e->getMessage(), PHP_MAJOR_VERSION.'-');
        }
    }

    /**
     * It should correcly format fatal error in nested dependency
     *
     * @test
     */
    public function should_correcly_format_fatal_error_in_nested_dependency()
    {
        $container = new Container();

        try {
            $container->get(DependingOnFatalError::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(ContainerException::class, $e);
            $this->assertNotInstanceOf(NotFoundException::class, $e);
            assertMatchesSnapshots($e->getMessage(), PHP_MAJOR_VERSION.'-');
        }
    }

    /**
     * It should correctly format fatal error in deep nested dependency
     *
     * @test
     */
    public function should_correctly_format_fatal_error_in_deep_nested_dependency()
    {
        $container = new Container();

        try {
            $container->get(Lorem::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(ContainerException::class, $e);
            $this->assertNotInstanceOf(NotFoundException::class, $e);
            assertMatchesSnapshots($e->getMessage(), PHP_MAJOR_VERSION.'-');
        }
    }

    /**
     * It should correctly handle single id binding of syntax error class
     *
     * @test
     */
    public function should_correctly_handle_single_id_binding_of_syntax_error_class()
    {
        $container = new Container();

        $this->expectException(ContainerException::class);

        $container->bind(FatalErrorClassSeven::class);
        $container->singleton(FatalErrorClassSeven::class);
    }
}
