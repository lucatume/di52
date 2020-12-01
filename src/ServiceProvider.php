<?php
/**
 * The base service provider class.
 */

namespace lucatume\DI52;

/**
 * Class ServiceProvider
 *
 * @package lucatume\DI52
 */
abstract class ServiceProvider {
	/**
	 * Whether the service provider will be a deferred one or not.
	 *
	 * @var bool
	 */
	protected $deferred = false;

	/**
	 * @var Container
	 */
	protected $container;


	/**
	 * ServiceProvider constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	/**
	 * Whether the service provider will be a deferred one or not.
	 *
	 * @return bool
	 */
	public function isDeferred() {
		return $this->deferred;
	}

	/**
	 * Returns an array of the class or interfaces bound and provided by the service provider.
	 *
	 * @return array
	 */
	public function provides() {
		return array();
	}

	/**
	 * Binds and sets up implementations at boot time.
	 */
	public function boot() {
		// no-op
	}
}
