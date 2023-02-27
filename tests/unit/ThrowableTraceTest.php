<?php

use lucatume\DI52\Container;
use lucatume\DI52\ContainerException;
use PHPUnit\Framework\TestCase;
use unit\data\ThrowErrorOnConstructClass;
use unit\data\ThrowExceptionOnConstructClass;
use unit\data\ThrowParseErrorOnConstructClass;

require_once __DIR__ . '/data/ThrowExceptionOnConstructClass.php';
require_once __dir__ . '/data/ThrowErrorOnConstructClass.php';
require_once __DIR__ . '/data/ThrowParseErrorOnConstructClass.php';

class ThrowableTraceTest extends TestCase
{

    public function exceptionExceptionMaskProviders()
    {
        return [
            'no masking' => [
                Container::EXCEPTION_MASK_NONE,
                \Exception::class,
                null
            ],
            'mask message' => [
                Container::EXCEPTION_MASK_MESSAGE,
                ContainerException::class,
                Exception::class
            ],
            'mask file and line' => [
                Container::EXCEPTION_MASK_FILE_LINE,
                ContainerException::class,
                Exception::class
            ],
            'mask message, file and line' => [
                Container::EXCEPTION_MASK_MESSAGE | Container::EXCEPTION_MASK_FILE_LINE,
                ContainerException::class,
                Exception::class
            ],
        ];
    }

    /**
     * @dataProvider exceptionExceptionMaskProviders
     */
    public function test_exception_casting(
        $maskThrowables,
        $expectedExceptionClass,
        $expectedPreviousExceptionClass
    ) {
        $this->expectException($expectedExceptionClass);

        try {
            $container = new Container();
            $container->setExceptionMask($maskThrowables);
            $container->get(ThrowExceptionOnConstructClass::class);
        } catch (Exception $e) {
            assertMatchesSnapshots(dumpThrowable($e));
            if ($expectedPreviousExceptionClass !== null) {
                $this->assertInstanceof($expectedPreviousExceptionClass, $e->getPrevious());
            } else {
                $this->assertNull($e->getPrevious());
            }
            throw $e;
        }
    }

    public function errorExceptionMaskProviders()
    {
        return [
            'no masking' => [
                Container::EXCEPTION_MASK_NONE,
                \Error::class,
                null
            ],
            'mask message' => [
                Container::EXCEPTION_MASK_MESSAGE,
                ContainerException::class,
                Error::class
            ],
            'mask file and line' => [
                Container::EXCEPTION_MASK_FILE_LINE,
                ContainerException::class,
                Error::class
            ],
            'mask message, file and line' => [
                Container::EXCEPTION_MASK_MESSAGE | Container::EXCEPTION_MASK_FILE_LINE,
                ContainerException::class,
                Error::class
            ],
        ];
    }

    /**
     * @requires PHP >=7.0
     *
     * @dataProvider errorExceptionMaskProviders
     */
    public function test_error_casting(
        $maskThrowables,
        $expectedExceptionClass,
        $expectedPreviousExceptionClass
    ) {
        $this->expectException($expectedExceptionClass);

        try {
            $container = new Container();
            $container->setExceptionMask($maskThrowables);
            $container->get(ThrowErrorOnConstructClass::class);
        } catch (Exception $e) {
            assertMatchesSnapshots(dumpThrowable($e));
            if ($expectedPreviousExceptionClass !== null) {
                $this->assertInstanceof($expectedPreviousExceptionClass, $e->getPrevious());
            } else {
                $this->assertNull($e->getPrevious());
            }
            throw $e;
        }
    }

    public function parseErrorExceptionMaskProviders()
    {
        return [
            'no masking' => [
                Container::EXCEPTION_MASK_NONE,
                \ParseError::class,
                null
            ],
            'mask message' => [
                Container::EXCEPTION_MASK_MESSAGE,
                ContainerException::class,
                ParseError::class
            ],
            'mask file and line' => [
                Container::EXCEPTION_MASK_FILE_LINE,
                ContainerException::class,
                ParseError::class
            ],
            'mask message, file and line' => [
                Container::EXCEPTION_MASK_MESSAGE | Container::EXCEPTION_MASK_FILE_LINE,
                ContainerException::class,
                ParseError::class
            ],
        ];
    }

    /**
     * @requires PHP >=7.0
     *
     * @dataProvider parseErrorExceptionMaskProviders
     */
    public function test_parse_error_casting(
        $maskThrowables,
        $expectedExceptionClass,
        $expectedPreviousExceptionClass
    ) {
        $this->expectException($expectedExceptionClass);

        try {
            $container = new Container();
            $container->setExceptionMask($maskThrowables);
            $container->get(ThrowParseErrorOnConstructClass::class);
        } catch (Exception $e) {
            assertMatchesSnapshots(dumpThrowable($e));
            if ($expectedPreviousExceptionClass !== null) {
                $this->assertInstanceof($expectedPreviousExceptionClass, $e->getPrevious());
            } else {
                $this->assertNull($e->getPrevious());
            }
            throw $e;
        }
    }
}
