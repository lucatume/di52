<?php


	class tad_DI52_ContainerTest extends PHPUnit_Framework_TestCase {

		/**
		 * @var string
		 */
		protected $sut_class = 'tad_DI52_Container';

		/**
		 * @var tad_DI52_Container
		 */
		protected $sut;

		public function setUp() {
			$this->sut = new tad_DI52_Container();
		}

		/**
		 * @test
		 * it should be instantiatable
		 */
		public function it_should_be_instantiatable() {
			$this->assertInstanceOf( $this->sut_class, $this->sut );
		}

		/**
		 * @test
		 * it should allow registering a variable
		 */
		public function it_should_allow_registering_a_variable() {
			$this->sut->set_var( 'foo', 23 );

			$this->assertEquals( 23, $this->sut->get_var( 'foo' ) );
		}

		/**
		 * @test
		 * it should allow setting a null value
		 */
		public function it_should_allow_setting_a_null_value() {
			$this->sut->set_var( 'foo' );

			$this->assertNull( $this->sut->get_var( 'foo' ) );
		}

		/**
		 * @test
		 * it should not allow setting a variable a second time
		 */
		public function it_should_not_allow_setting_a_variable_a_second_time() {
			$this->sut->set_var( 'foo', 23 );

			$this->assertEquals( 23, $this->sut->get_var( 'foo' ) );
			$this->sut->set_var( 'foo', 'new value' );

			$this->assertEquals( 23, $this->sut->get_var( 'foo' ) );
		}

		/**
		 * @test
		 * it should throw if trying to get non set var
		 */
		public function it_should_throw_if_trying_to_get_non_set_var() {
			$this->setExpectedException( 'InvalidArgumentException' );
			$this->sut->get_var( 'foo' );
		}

		/**
		 * @test
		 * it should allow registering a constructor
		 */
		public function it_should_allow_registering_a_constructor() {
			$this->sut->set_ctor( 'object', 'tad_DI52_MyObject' );

			$object = $this->sut->make( 'object' );

			$this->assertInstanceOf( 'tad_DI52_MyObject', $object );
		}

		/**
		 * @test
		 * it should return a new instance of an object on each make call
		 */
		public function it_should_return_a_new_instance_of_an_object_on_each_make_call() {
			$this->sut->set_ctor( 'object', 'tad_DI52_MyObject' );

			$object1 = $this->sut->make( 'object' );
			$object2 = $this->sut->make( 'object' );

			$this->assertNotSame( $object1, $object2 );
		}

		/**
		 * @test
		 * it should allow specifying a constructor method
		 */
		public function it_should_allow_specifying_a_constructor_method() {
			$class = 'tad_DI52_MyObject';

			$this->sut->set_ctor( 'object', $class . '::create' );

			$object = $this->sut->make( 'object' );

			$this->assertInstanceOf( 'tad_DI52_MyObject', $object );
		}

		/**
		 * @test
		 * it should allow specifying constructor arguments
		 */
		public function it_should_allow_specifying_constructor_arguments() {
			$class = 'tad_DI52_MySecondObject';

			$this->sut->set_ctor( 'object', $class, array(
				'foo',
				23
			) );

			$object = $this->sut->make( 'object' );

			$this->assertInstanceOf( $class, $object );
			$this->assertEquals( 'foo', $object->string );
			$this->assertEquals( 23, $object->int );
		}

		/**
		 * @test
		 * it should allow specifying static constructor arguments
		 */
		public function it_should_allow_specifying_static_constructor_arguments() {
			$class = 'tad_DI52_MyThirdObject';

			$this->sut->set_ctor( 'object', $class . '::one', array(
				'foo',
				23
			) );

			$object = $this->sut->make( 'object' );

			$this->assertInstanceOf( $class, $object );
			$this->assertEquals( 'foo', $object->string );
			$this->assertEquals( 23, $object->int );
		}

		/**
		 * @test
		 * it should allow specifying previously registered vars as args
		 */
		public function it_should_allow_specifying_previously_registered_vars_as_args() {
			$class = 'tad_DI52_MyThirdObject';
			$this->sut->set_var( 'string', 'foo' );
			$this->sut->set_var( 'int', 23 );

			$args = array(
				'#string',
				'#int'
			);
			$this->sut->set_ctor( 'object', $class . '::one', $args );

			$object = $this->sut->make( 'object' );

			$this->assertInstanceOf( $class, $object );
			$this->assertEquals( 'foo', $object->string );
			$this->assertEquals( 23, $object->int );
		}

		/**
		 * @test
		 * it should allow specifying previously registered objects as args
		 */
		public function it_should_allow_specifying_previously_registered_objects_as_args() {
			$class = 'tad_DI52_MyFourthObject';

			$this->sut->set_ctor( 'myObject', 'tad_DI52_MyObject' );
			$this->sut->set_var( 'string', 'foo' );

			$args = array(
				'@myObject',
				'#string'
			);
			$this->sut->set_ctor( 'dependingObject', 'tad_DI52_MyFourthObject::create', $args );

			$object = $this->sut->make( 'dependingObject' );

			$this->assertInstanceOf( $class, $object );
			$this->assertInstanceOf( 'tad_DI52_MyObject', $object->myObject );
			$this->assertEquals( 'foo', $object->string );
		}

		/**
		 * @test
		 * it should allow setting a singleton instance
		 */
		public function it_should_allow_setting_a_singleton_instance() {
			$class = 'tad_DI52_MyObject';

			$this->sut->set_shared( 'singleton', $class );

			$i1 = $this->sut->make( 'singleton' );
			$i2 = $this->sut->make( 'singleton' );

			$this->assertSame( $i1, $i2 );
		}

		/**
		 * @test
		 * it should allow setting a singleton instance using set vars
		 */
		public function it_should_allow_setting_a_singleton_instance_using_set_vars() {
			$class = 'tad_DI52_MySecondObject';

			$this->sut->set_var( 'string', 'foo' );
			$this->sut->set_var( 'int', 23 );

			$args = array(
				'#string',
				'#int'
			);
			$this->sut->set_shared( 'singleton', $class, $args );

			$i1 = $this->sut->make( 'singleton' );
			$i2 = $this->sut->make( 'singleton' );

			$this->assertSame( $i1, $i2 );
		}

		/**
		 * @test
		 * it should allow setting a singleton using set instances
		 */
		public function it_should_allow_setting_a_singleton_using_set_instances() {
			$class = 'tad_DI52_MyFourthObject';

			$this->sut->set_ctor( 'myObject', 'tad_DI52_MyObject::create' );

			$args = array(
				'@myObject',
				'foo'
			);
			$this->sut->set_shared( 'singleton', $class . '::create', $args );

			$i1 = $this->sut->make( 'singleton' );
			$i2 = $this->sut->make( 'singleton' );

			$this->assertSame( $i1, $i2 );
		}

		/**
		 * @test
		 * it should allow to set methods to be called after the contstructor method
		 */
		public function it_should_allow_to_set_methods_to_be_called_after_the_contstructor_method() {
			$class = 'tad_DI_MyFifthObject';

			$this->sut->set_ctor( 'object', $class )->setDependency( new tad_DI_Dependency() )->setString( 'foo' )
			          ->setInt( 23 );
			$i = $this->sut->make( 'object' );

			$this->assertInstanceOf( $class, $i );
			$this->assertInstanceOf( 'tad_DI_Dependency', $i->dependency );
			$this->assertEquals( 'foo', $i->string );
			$this->assertEquals( 23, $i->int );
		}

		/**
		 * @test
		 * it should allow specifying methods to call after constructors and refer previuosly registered arguments
		 */
		public function it_should_allow_specifying_methods_to_call_after_constructors_and_refer_previuosly_registered_arguments() {
			$class = 'tad_DI_MyFifthObject';

			$this->sut->set_ctor( 'dependency', 'tad_DI_Dependency' );
			$this->sut->set_var( 'string', 'foo' );
			$this->sut->set_var( 'int', 23 );
			$this->sut->set_ctor( 'object', $class )->setDependency( '@dependency' )->setString( '#string' )
			          ->setInt( '#int' );

			$i = $this->sut->make( 'object' );

			$this->assertInstanceOf( $class, $i );
			$this->assertInstanceOf( 'tad_DI_Dependency', $i->dependency );
			$this->assertEquals( 'foo', $i->string );
			$this->assertEquals( 23, $i->int );
		}
	}


	class tad_DI52_MyObject {

		public static function create() {
			return new self;
		}
	}


	class tad_DI52_MySecondObject {

		public $string;
		public $int;

		public function __construct( $string, $int ) {
			$this->string = $string;
			$this->int = $int;
		}
	}


	class tad_DI52_MyThirdObject {

		public $string;
		public $int;

		public static function one( $string, $int ) {
			$i = new self;
			$i->string = $string;
			$i->int = $int;

			return $i;
		}
	}


	class tad_DI52_MyFourthObject {

		public $myObject;
		public $string;

		public static function create( tad_DI52_MyObject $myObject, $string ) {
			$i = new self;
			$i->myObject = $myObject;
			$i->string = $string;

			return $i;
		}
	}


	class tad_DI_Dependency {

	}


	class tad_DI_MyFifthObject {

		public $dependency;
		public $string;
		public $int;

		public function setDependency( tad_DI_Dependency $dependency ) {
			$this->dependency = $dependency;
		}

		public function setString( $string ) {
			$this->string = $string;
		}

		public function setInt( $int ) {
			$this->int = $int;
		}
	}
