<?php

namespace Builders;

use lucatume\DI52\Builders\Parameter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use UnionTypeClass;
use UnionTypePromotedClass;

class UnionTypeParameterTest extends TestCase {

    /**
     * @beforeClass
     */
    public static function before_all()
    {
        if (PHP_VERSION_ID < 80000) {
            return;
        }

        require_once __DIR__.'/parameter-test-union-type-classes.php';
    }

    /**
     * @before
     */
    public function before_each()
    {
        if (PHP_VERSION_ID < 80000) {
            $this->markTestSkipped();
        }
    }
    public function test_it_should_detect_union_types()
    {
        $expectedData = [
            [
                'type' => 'union',
                'isOptional' => false,
                'defaultValue' => null,
            ]
        ];
        $reflectionConstructor = new ReflectionMethod(UnionTypeClass::class, '__construct');
        foreach ($reflectionConstructor->getParameters() as $i => $p) {
            $parameter = new Parameter($i, $p);
            $data = $parameter->getData();
            $this->assertNull($parameter->getClass());
            $this->assertEquals($expectedData[$i]['type'], $parameter->getType());
            $this->assertEquals($expectedData[$i]['defaultValue'], $parameter->getDefaultValue());
            $this->assertEquals($expectedData[$i], $data, 'Parameter #'.$i." ($p->name)");
        }
    }

    public function test_it_should_detect_union_types_with_constructor_promotion()
    {
        $expectedData = [
            [
                'type' => 'union',
                'isOptional' => false,
                'defaultValue' => null,
            ]
        ];
        $reflectionConstructor = new ReflectionMethod(UnionTypePromotedClass::class, '__construct');
        foreach ($reflectionConstructor->getParameters() as $i => $p) {
            $parameter = new Parameter($i, $p);
            $data = $parameter->getData();
            $this->assertNull($parameter->getClass());
            $this->assertEquals($expectedData[$i]['type'], $parameter->getType());
            $this->assertEquals($expectedData[$i]['defaultValue'], $parameter->getDefaultValue());
            $this->assertEquals($expectedData[$i], $data, 'Parameter #'.$i." ($p->name)");
        }
    }

}
