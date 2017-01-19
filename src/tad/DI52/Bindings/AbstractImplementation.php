<?php

abstract class tad_DI52_Bindings_AbstractImplementation
{
	/**
	 * @var tad_DI52_Container
	 */
	protected $container;
	/**
	 * @var tad_DI52_Bindings_ResolverInterface
	 */
	protected $resolver;
	/**
	 * @var string
	 */
	protected $implementation;

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * tad_DI52_Bindings_ConstructorImplementation constructor.
	 *
	 * @param string                              $implementation
	 * @param tad_DI52_Container                  $container
	 * @param tad_DI52_Bindings_ResolverInterface $resolver
	 */
	public function __construct($implementation, tad_DI52_Container $container, tad_DI52_Bindings_ResolverInterface $resolver)
	{
		$this->implementation = $implementation;
		$this->container = $container;
		$this->resolver = $resolver;
		$this->id = uniqid() . rand(1, 9999);
	}

	/**
	 * @return mixed
	 */
	public function getImplementation()
	{
		return $this->implementation;
	}

	/**
	 * @return string Returns the implementation id
	 */
	public function getId()
	{
		return $this->id;
	}
}
