<?php


class tad_DI52_Container implements ArrayAccess, tad_DI52_ContainerInterface
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public function setVar($key, $value)
    {
        // TODO: Implement setVar() method.
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getVar($key)
    {
        // TODO: Implement getVar() method.
    }

    /**
     * Binds an interface or class to an implementation.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function bind($classOrInterface, $implementation, array $afterBuildMethods = null)
    {
        // TODO: Implement bind() method.
    }

    /**
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function make($classOrInterface)
    {
        // TODO: Implement make() method.
    }

    /**
     * Binds an interface or class to an implementation and will always return the same instance.
     *
     * @param string $classOrInterface
     * @param string $implementation
     * @param array $afterBuildMethods
     */
    public function singleton($classOrInterface, $implementation, array $afterBuildMethods = null)
    {
        // TODO: Implement singleton() method.
    }

    /**
     * Tags an array of implementation bindings.
     *
     * @param array $implementationsArray
     * @param string $tag
     */
    public function tag(array $implementationsArray, $tag)
    {
        // TODO: Implement tag() method.
    }

    /**
     * Retrieves an array of bound implementations resolving them.
     *
     * @param string $tag
     * @return array An array of resolved bound implementations.
     */
    public function tagged($tag)
    {
        // TODO: Implement tagged() method.
    }

    /**
     * Registers a service provider implementation.
     *
     * @param string $serviceProviderClass
     */
    public function register($serviceProviderClass)
    {
        // TODO: Implement register() method.
    }

    /**
     * Boots up the application calling the `boot` method of each registered service provider.
     */
    public function boot()
    {
        // TODO: Implement boot() method.
    }

    /**
     * Checks whether if an interface or class has been bound to a concrete implementation.
     *
     * @param string $classOrInterface
     * @return bool
     */
    public function isBound($classOrInterface)
    {
        // TODO: Implement isBound() method.
    }

    /**
     * Checks whether a tag group exists in the container.
     *
     * @param string $tag
     * @return bool
     */
    public function hasTag($tag)
    {
        // TODO: Implement hasTag() method.
    }

    /**
     * Binds a chain of decorators to a class or interface.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function bindDecorators($classOrInterface, array $decorators)
    {
        // TODO: Implement bindDecorators() method.
    }

    /**
     * Binds a chain of decorators to a class or interface to be returned as a singleton.
     *
     * @param $classOrInterface
     * @param array $decorators
     */
    public function singletonDecorators($classOrInterface, $decorators)
    {
        // TODO: Implement singletonDecorators() method.
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    /**
     * Starts the `whenRequiredBy->[bind|singleton]` chain for a contextual binding.
     *
     * @param string $class The fully qualified name of the requesting class.
     *
     * Example:
     *
     *      // any class requesting an implementation of `LoggerInterface` will receive this implementation...
     *      $container->singleton('LoggerInterface', 'FilesystemLogger');
     *      // but if the requesting class is `Worker` return another implementation
     *      $container->whenRequestedBy('Worker')
     *          ->singleton('LoggerInterface', 'RemoteLogger');
     *
     * @return tad_DI52_ContainerInterface
     */
    public function whenRequiredBy($class)
    {
        // TODO: Implement whenRequiredBy() method.
    }
}
