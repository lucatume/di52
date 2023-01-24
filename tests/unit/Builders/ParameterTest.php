<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package Builders
 */

namespace Builders;

use lucatume\DI52\Builders\Parameter;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ParameterTest extends TestCase
{
    /**
     * @beforeClass
     */
    public static function before_all()
    {
        if (PHP_VERSION_ID < 70000) {
            return;
        }

        require_once __DIR__ . '/parameter-test-classes.php';
        require_once __DIR__ . '/parameter-test-ns-classes.php';
    }

    /**
     * @before
     */
    public function before_each()
    {
        if (PHP_VERSION_ID < 70000) {
            $this->markTestSkipped();
        }

        require_once __DIR__ . '/parameter-test-classes.php';
        require_once __DIR__ . '/parameter-test-ns-classes.php';
    }

    public function parameter_building_for_scalars_data_provider()
    {
        return [
            'string' => [
                \ParameterTestClassOne::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'string',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => 'three'
                    ],
                    [
                        'type' => 'string',
                        'isOptional' => true,
                        'defaultValue' => 'four'
                    ]
                ]
            ],
            'int' => [
                \ParameterTestClassTwo::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'int',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => 3
                    ],
                    [
                        'type' => 'int',
                        'isOptional' => true,
                        'defaultValue' => 4
                    ]
                ]
            ],
            'bool' => [
                \ParameterTestClassThree::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'bool',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => true
                    ],
                    [
                        'type' => 'bool',
                        'isOptional' => true,
                        'defaultValue' => false
                    ]
                ]
            ],
            'float' => [
                \ParameterTestClassFour::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'float',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => 2.3
                    ],
                    [
                        'type' => 'float',
                        'isOptional' => true,
                        'defaultValue' => 8.9
                    ]
                ]
            ],
            'array' => [
                \ParameterTestClassFive::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'array',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => []
                    ],
                    [
                        'type' => 'array',
                        'isOptional' => true,
                        'defaultValue' => ['four', 'five' => 'six']
                    ]
                ]
            ],
            'callable' => [
                \ParameterTestClassSix::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'callable',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => null
                    ],
                    [
                        'type' => 'callable',
                        'isOptional' => true,
                        'defaultValue' => null
                    ]
                ]
            ],
            'iterable' => [
                \ParameterTestClassSeven::class,
                [
                    [
                        'type' => null,
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => ( PHP_VERSION_ID < 80200 ) ? 'iterable' : 'union',
                        'isOptional' => false,
                        'defaultValue' => null
                    ],
                    [
                        'type' => null,
                        'isOptional' => true,
                        'defaultValue' => null
                    ],
                    [
                        'type' => ( PHP_VERSION_ID < 80200 ) ? 'iterable' : 'union',
                        'isOptional' => true,
                        'defaultValue' => null
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider parameter_building_for_scalars_data_provider
     */
    public function test_parameter_building_for_scalars($class, $expectedData)
    {
        $reflectionConstructor = new ReflectionMethod($class, '__construct');
        foreach ($reflectionConstructor->getParameters() as $i => $p) {
            $parameter = new Parameter($i, $p);
            $data = $parameter->getData();
            $this->assertNull($parameter->getClass());
            $this->assertEquals($expectedData[$i]['defaultValue'], $parameter->getDefaultValue());
            $this->assertEquals($expectedData[$i], $data, 'Parameter #' . $i . " ({$p->name})");
        }
    }

    public function test_parameter_building_for_global_classes()
    {
        $expectedData = [
            [
                'type' => \ParameterTestClassOne::class,
                'isOptional' => false,
                'defaultValue' => null
            ],
            [
                'type' => \ParameterTestClassTwo::class,
                'isOptional' => true,
                'defaultValue' => null
            ]
        ];
        $reflectionConstructor = new ReflectionMethod(\ParameterTestClassEight::class, '__construct');
        foreach ($reflectionConstructor->getParameters() as $i => $p) {
            $parameter = new Parameter($i, $p);
            $data = $parameter->getData();
            $this->assertEquals($expectedData[$i]['type'], $parameter->getClass());
            $this->assertEquals($expectedData[$i]['defaultValue'], $parameter->getDefaultValue());
            $this->assertEquals($expectedData[$i], $data, 'Parameter #' . $i . " ({$p->name})");
        }
    }

    public function test_parameter_building_for_namespaced_classes()
    {
        $expectedData = [
            [
                'type' => \Parameter\Test\ClassOne::class,
                'isOptional' => false,
                'defaultValue' => null
            ],
            [
                'type' => \Parameter\Test\ClassTwo::class,
                'isOptional' => true,
                'defaultValue' => null
            ]
        ];
        $reflectionConstructor = new ReflectionMethod(\Parameter\Test\ClassThree::class, '__construct');
        foreach ($reflectionConstructor->getParameters() as $i => $p) {
            $parameter = new Parameter($i, $p);
            $data = $parameter->getData();
            $this->assertEquals($expectedData[$i]['type'], $parameter->getClass());
            $this->assertEquals($expectedData[$i]['defaultValue'], $parameter->getDefaultValue());
            $this->assertEquals($expectedData[$i], $data, 'Parameter #' . $i . " ({$p->name})");
        }
    }
}
