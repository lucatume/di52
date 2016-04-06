<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

// include test classes
$files = [
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
];
foreach ($files as $file) {
    include_once dirname(__FILE__) . '/data/' . $file;
}
