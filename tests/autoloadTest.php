<?php
namespace lucatume\DI52;

use PHPUnit\Framework\TestCase;

class autoloadTest extends TestCase {

	public function prefixed_classes_data_provider(  ) {
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
	public function should_correctly_autoload_tad_di_52_prefixed_classes($targetClass,$expected) {
		$this->assertTrue( class_exists( $targetClass ) );
	}
}
