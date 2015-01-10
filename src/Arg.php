<?php


	class tad_DI52_Arg {

		protected $arg;

		/** @var  tad_DI52_Container */
		protected $container;

		public static function create( $arg, tad_DI52_Container $container ) {
			$instance = new static;
			$instance->arg = $arg;
			$instance->container = $container;

			return $instance;
		}

		/**
		 * @return bool
		 */
		protected function is_referred_value() {
			$matches = array();
			$is_referred_value = is_string( $this->arg ) && preg_match( '/^(@|#|~)(.+)/', $this->arg, $matches );

			return array(
				$is_referred_value,
				$matches
			);
		}

		/**
		 * @param $matches
		 *
		 * @return mixed|object
		 */
		protected function get_referred_value( $matches ) {
			$type = $matches[1];
			$alias_or_class_name = $matches[2];

			switch ( $type ) {
				case '@':
					$value = $this->container->make( $alias_or_class_name );
					break;
				case '#':
					$value = $this->container->get_var( $alias_or_class_name );
					break;
				case '~':
					$value = new $alias_or_class_name();
					break;
			}

			return $value;
		}

		public function get_value() {
			list( $is_referred_value, $matches ) = $this->is_referred_value();
			if ( $is_referred_value ) {
				$value = $this->get_referred_value( $matches );
			} else {
				$value = $this->arg;
			}

			return $value;
		}
	}