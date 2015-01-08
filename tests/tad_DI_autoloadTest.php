<?php
	require_once dirname( dirname( __FILE__ ) ) . '/autoload.php';


	class tad_DI52_autoloadTest extends PHPUnit_Framework_TestCase {

		public function classes() {
			$dir = dirname( dirname( __FILE__ ) ) . '/src';

			return array(
				array(
					'tad_DI52_Arg',
					$dir . '/Arg.php'
				),
				array(
					'tad_DI52_Container',
					$dir . '/Container.php'
				),
				array(
					'tad_DI52_Ctor',
					$dir . '/Ctor.php'
				),
				array(
					'tad_DI52_Singleton',
					$dir . '/Singleton.php'
				),
				array(
					'tad_DI52_Var',
					$dir . '/Var.php'
				)
			);
		}

		/**
		 * @test
		 * it should locate each class of the DI package
		 * @dataProvider classes
		 */
		public function it_should_locate_each_class_of_the_di_package( $class, $path ) {
			$this->assertEquals( $path, __tad_DI52_get_file_path( $class ) );
		}
	}
