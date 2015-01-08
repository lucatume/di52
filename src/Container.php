<?php


	class tad_DI52_Container {

		/**
		 * @var tad_DI52_Var[]
		 */
		protected $vars = array();

		/**
		 * @var tad_DI52_Ctor[]
		 */
		protected $ctors = array();

		public function set_var( $alias, $value = null ) {
			if ( ! isset( $this->vars[ $alias ] ) ) {
				$this->vars[ $alias ] = tad_DI52_Var::create( $value );
			}

			return $this;
		}

		public function get_var( $alias ) {
			$this->assert_var_alias( $alias );

			return $this->vars[ $alias ]->get_value();
		}

		public function set_ctor( $alias, $class_and_method, array $args = array() ) {
			if ( ! isset( $this->ctors[ $alias ] ) ) {
				$this->ctors[ $alias ] = tad_DI52_Ctor::create( $class_and_method, $args, $this );
			}

			return $this;
		}

		public function make( $alias ) {
			$this->assert_ctor_alias( $alias );

			$ctor = $this->ctors[ $alias ];

			$instance = $ctor->get_object_instance();

			return $instance;
		}

		/**
		 * @param $alias
		 */
		protected function assert_ctor_alias( $alias ) {
			if ( ! array_key_exists( $alias, $this->ctors ) ) {
				throw new InvalidArgumentException( "No constructor with the $alias alias is registered" );
			}
		}

		/**
		 * @param $alias
		 */
		protected function assert_var_alias( $alias ) {
			if ( ! array_key_exists( $alias, $this->vars ) ) {
				throw new InvalidArgumentException( "No variable with the $alias alias is registered" );
			}
		}

		public function set_shared( $alias, $class_and_method, array $args = array() ) {
			if ( ! isset( $this->ctors[ $alias ] ) ) {
				$this->ctors[ $alias ] = tad_DI52_Singleton::create( $class_and_method, $args, $this );
			}

			return $this;
		}
	}