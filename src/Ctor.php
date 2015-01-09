<?php


	class tad_DI52_Ctor {

		/** @var  string */
		protected $class;
		/** @var  string */
		protected $method;

		/** @var  tad_DI52_Arg[] */
		protected $args = array();

		/** @var  tad_DI52_Container */
		protected $container;

		/** @var  array */
		protected $calls;

		public static function create( $class_and_method, array $args = array(), tad_DI52_Container $container ) {
			$instance = new static();
			list( $class, $method ) = $instance->get_class_and_method( $class_and_method );
			$instance->class = $class;
			$instance->method = $method;
			$instance->container = $container;

			foreach ( $args as $arg ) {
				$instance->args[] = tad_DI52_Arg::create( $arg, $instance->container );
			}

			return $instance;
		}

		public function __call( $method, array $args = null ) {
			$_args = array();
			if ( ! empty( $args ) ) {
				foreach ( $args as $value ) {
					$_args[] = tad_DI52_Arg::create( $value, $this->container );
				}
			}
			$this->calls[] = array(
				$method,
				$_args
			);

			return $this;
		}

		protected function get_class_and_method( $class_and_method ) {
			if ( ! is_string( $class_and_method ) ) {
				throw new InvalidArgumentException( "Class and method should be a single string" );
			}
			$frags = explode( '::', $class_and_method );
			if ( count( $frags ) > 2 ) {
				throw new InvalidArgumentException( "One :: separator only" );
			}

			return count( $frags ) === 1 ? array(
				$frags[0],
				'__construct'
			) : $frags;
		}

		public function get_object_instance() {
			$args = $this->get_arg_values();

			$instance = $this->create_instance( $args );

			if ( ! empty( $this->calls ) ) {
				foreach ( $this->calls as $call ) {
					$arg_values = array();
					foreach ( $call[1] as $arg ) {
						$arg_values[] = $arg->get_value();
					}
					call_user_func_array( array(
						$instance,
						$call[0]
					), $arg_values );
				}
			}

			return $instance;
		}

		private function get_arg_values() {
			$values = array();
			foreach ( $this->args as $arg ) {
				$values[] = $arg->get_value();
			}

			return $values;
		}

		/**
		 * @param $args
		 *
		 * @return mixed|object
		 */
		protected function create_instance( $args ) {
			if ( $this->method === '__construct' ) {
				$rc = new ReflectionClass( $this->class );

				return $rc->newInstanceArgs( $args );
			}

			return call_user_func_array( array(
				$this->class,
				$this->method
			), $args );
		}
	}