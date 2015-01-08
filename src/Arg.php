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

		private function parse_value( $arg ) {
			$matches = array();
			if ( preg_match( '/^(@|#)(.+)/', $arg, $matches ) ) {
				$type = $matches[1];
				$alias = $matches[2];

				return $type === '@' ? $this->container->make( $alias ) : $this->container->get_var( $alias );
			}

			return $arg;
		}

		public function get_value() {
			return $this->parse_value( $this->arg );
		}
	}