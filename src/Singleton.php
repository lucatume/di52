<?php


	class tad_DI52_Singleton extends tad_DI52_Ctor {

		/**
		 * @var mixed
		 */
		protected $instance;

		public function get_object_instance() {
			if ( empty( $this->instance ) ) {
				$instance = parent::get_object_instance();
				$this->instance = $instance;
			}

			return $this->instance;
		}

	}