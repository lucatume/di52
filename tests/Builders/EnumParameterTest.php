<?php

namespace Builders;

use ClassWithEnumDependency;
use lucatume\DI52\Builders\Parameter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TestBackedEnum;
use UnionTypeEnumClass;

class EnumParameterTest extends TestCase
{

    /**
     * @beforeClass
     */
    public static function before_all()
    {
        if (PHP_VERSION_ID < 80100) {
            return;
        }

        require_once __DIR__.'/parameter-test-enum-classes.php';
    }

    /**
     * @before
     */
    public function before_each()
    {
        if (PHP_VERSION_ID < 80100) {
            $this->markTestSkipped();
        }
    }

    public function test_it_should_detect_enum_types()
    {
        $expectedData = [
            [
                'type' => TestBackedEnum::class,
                'isOptional' => false,
                'defaultValue' => null,
            ]
        ];
        $reflectionConstructor = new ReflectionMethod(ClassWithEnumDependency::class, '__construct');
        foreach ($reflectionConstructor->getParameters() as $i => $p) {
            $parameter = new Parameter($i, $p);
            $data = $parameter->getData();
            $this->assertNull($parameter->getClass());
            $this->assertEquals($expectedData[$i]['type'], $parameter->getType());
            $this->assertEquals($expectedData[$i]['defaultValue'], $parameter->getDefaultValue());
            $this->assertEquals($expectedData[$i], $data, 'Parameter #'.$i." ($p->name)");
        }
    }

    public function test_it_should_detect_union_types_with_enums()
    {
        $expectedData = [
            [
                'type' => 'union',
                'isOptional' => false,
                'defaultValue' => null,
            ]
        ];
        $reflectionConstructor = new ReflectionMethod(UnionTypeEnumClass::class, '__construct');
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
