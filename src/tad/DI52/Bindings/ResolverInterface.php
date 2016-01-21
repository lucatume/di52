<?php

interface tad_DI52_Bindings_ResolverInterface
{
    /**
     * Binds an interface or class to an implementation.
     *
     * @param string $interfaceOrClass
     * @param string $implementation
     * @param bool $skipImplementationCheck Whether the implementation should be checked as valid implementation or
     * extension of the class.
     */
    public function bind($interfaceOrClass, $implementation, $skipImplementationCheck = false);

    /**
     * Returns an instance of the class or object bound to an interface.
     *
     * @param string $classOrInterface A fully qualified class or interface name.
     * @return mixed
     */
    public function resolve($classOrInterface);
}