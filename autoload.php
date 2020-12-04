<?php
/**
 * Registers the autoload function for the library.
 * The function will take care of redirecting calls to the `tad_DI52_`, non-namespaced, class format to the namespaced
 * classes.
 */

namespace lucatume\DI52;

/**
 * Locates and load a library class in its namespaced or non-namespaced format.
 *
 * @param string $class The fully qualified name of the class to try and locate.
 *
 * @return bool Whether the class was located and loaded or not.
 */

spl_autoload_register(static function ($class) {
    if (strpos($class, 'tad_DI52_') === false) {
        return false;
    }

    $className = str_replace('tad_DI52_', '', $class);
    // This should be handled by Composer, but just in case handle it here too.
    $path = __DIR__ . '/src/' . $className . '.php';

    if (! is_file($path)) {
        return false;
    }

    /** @noinspection PhpIncludeInspection */
    require_once $path;
    $loadedClass = '\\lucatume\\DI52\\' . $className;
    class_alias($loadedClass, $class);

    return true;
});
