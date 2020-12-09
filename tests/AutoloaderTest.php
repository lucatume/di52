<?php
namespace lucatume\DI52;

use PHPUnit\Framework\TestCase;

class AutoloaderTest extends TestCase
{

    public function prefixed_classes_data_provider()
    {
        return [
            'tad_DI52_Container' => ['tad_DI52_Container',\lucatume\DI52\Container::class],
            'tad_DI52_ProtectedValue' => ['tad_DI52_ProtectedValue',\lucatume\DI52\ProtectedValue::class],
            'tad_DI52_ServiceProvider' => ['tad_DI52_ServiceProvider',\lucatume\DI52\ServiceProvider::class]
        ];
    }
    /**
     * It should correctly autoload tad_DI52_ prefixed classes
     *
     * @test
     * @dataProvider prefixed_classes_data_provider
     */
    public function should_correctly_autoload_tad_di_52_prefixed_classes($targetClass, $expected)
    {
        $this->assertTrue(class_exists($targetClass));
    }

    /**
     * It should not be able to locate non project class
     *
     * @test
     */
    public function should_not_be_able_to_locate_non_project_class()
    {
        $autoloader = new Autoloader();

        $this->assertNull($autoloader->locateClass('Foo_Baz_Bar'));
    }

    /**
     * It should not be able to locate non existing project prefixed class
     *
     * @test
     */
    public function should_not_be_able_to_locate_non_existing_project_prefixed_class()
    {
        $autoloader = new Autoloader();

        $this->assertNull($autoloader->locateClass('tad_DI52_Something'));
    }
}
