<?php

namespace lucatume\DI52\Tests;

if (class_exists('\\PHPUnit\\Framework\\TestCase')) {
    class TestCase extends \PHPUnit\Framework\TestCase
    {

    }
} elseif (class_exists('\\')) {
    class TestCase extends PHPUnit_Framework_TestCase
    {

    }
} else {
    throw new \RuntimeException('No base test case found.');
}
