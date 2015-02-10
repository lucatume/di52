<?php


	class tad_DI52_Var {

		protected $value;

		public function __construct() {
		}

		public static function create( $value = null ) {
			$instance = new static;
			$instance->set_value( $value );

			return $instance;
		}

		public function get_value() {
			return $this->value;
		}

		public function set_value( $value ) {
			$this->value = $value;
		}
	}