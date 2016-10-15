<?php

$vendorDir = dirname(__DIR__) . '/vendor';
require_once $vendorDir . '/phpunit/php-text-template/Text/Template/Autoload.php';
require_once $vendorDir . '/phpunit/php-token-stream/PHP/Token/Stream/Autoload.php';
require_once $vendorDir . '/phpunit/php-file-iterator/File/Iterator/Autoload.php';
require_once $vendorDir . '/phpunit/php-timer/PHP/Timer/Autoload.php';
require_once $vendorDir . '/phpunit/php-code-coverage/PHP/CodeCoverage/Autoload.php';
require_once $vendorDir . '/phpunit/phpunit/PHPUnit/Autoload.php';

// include test classes
$files = array(
    'TestInterfaceOne.php',
    'TestInterfaceTwo.php',
    'InterfaceOneAndTwoImplementation.php',
    'TestInterfaceThree.php',
    'ConcreteClassImplementingTestInterfaceOne.php',
    'ConcreteClassImplementingTestInterfaceTwo.php',
    'ConcreteClassOne.php',
    'DependencyObjectOne.php',
    'DependingClassOne.php',
    'DependingClassThree.php',
    'DependingClassTwo.php',
    'ExtendingClassOne.php',
    'ObjectFive.php',
    'ObjectFour.php',
    'ObjectOne.php',
    'ObjectThree.php',
    'ObjectTwo.php',
    'PrimitiveDependingClassOne.php',
    'PrimitiveDependingClassTwo.php',
    'ClassOne.php',
    'ClassOneWithCounter.php',
    'RequiringOne.php',
    'RequiringOneWithCounter.php',
    'ClassTwo.php',
    'ClassThree.php',
    'ServiceProviderOne.php',
    'ServiceProviderTwo.php',
    'DeferredServiceProviderOne.php',
    'DeferredServiceProviderTwo.php',
    'DeferredServiceProviderThree.php',
    'BaseClassInterface.php',
    'BaseClass.php',
    'BaseClassDecoratorOne.php',
    'BaseClassDecoratorThree.php',
    'BaseClassDecoratorTwo.php',
    'CustomClassOne.php',
    'CustomClassTwo.php',
    'CustomClassOneExtension.php',
    'CustomClassThree.php',
);
foreach ($files as $file) {
    include_once dirname(__FILE__) . '/data/' . $file;
}
