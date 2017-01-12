<?php
/**
 * Builds and returns a closure to be used to build to lazily make objects on PHP 5.3+.
 *
 * @param tad_DI52_Container $container
 * @param string $classOrInterface
 * @param string $method
 *
 * @return Closure
 */
function di52_LazyMakeClosure(tad_DI52_Container $container, $classOrInterface, $method)
{
    return function () use ($container, $classOrInterface, $method) {
        $a = func_get_args();
        $i = $container->make($classOrInterface);
        return call_user_func_array(array($i, $method), $a);
    };
}
