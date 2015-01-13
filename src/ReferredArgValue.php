<?php


abstract class tad_DI52_ReferredArgValue {

	protected $alias;
	protected $container;

	public static function create( $alias, tad_DI52_Container $container ) {
		$instance = new static;

		$instance->alias = $alias;
		$instance->container = $container;

		return $instance;
	}

	abstract public function get_value();
}