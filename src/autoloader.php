<?php
/**
 * The library autoload and alias handler.
 */

namespace lucatume\DI52;

/**
 * Class Autoloader
 *
 * @package lucatume\DI52
 */
class Autoloader
{

    /**
     * Locates and load a library class in its namespaced or non-namespaced format.
     *
     * @param string $class The fully qualified name of the class to try and locate.
     *
     * @return void The method does not return any value.
     */
    public function __invoke($class)
    {
        if (! $path = $this->locateClass($class)) {
            return;
        }

        /** @noinspection PhpIncludeInspection */
        require_once $path;
        $loadedClass = '\\lucatume\\DI52\\' . $this->getClassName($class);
        class_alias($loadedClass, $class);
    }

    /**
     * Locates a project class file from the class name.
     *
     * @param string $class The fully-qualified class name to locate the file for.
     *
     * @return string|null Either the absolute path to the class file, or `null` if not found.
     */
    public function locateClass($class)
    {
        if (strpos($class, 'tad_DI52_') === false) {
            return null;
        }

        // This should be handled by Composer, but just in case handle it here too.
        $path = __DIR__ . '/' . $this->getClassName($class) . '.php';

        if (! is_file($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Returns the class name from the prefixed version of the class name.
     *
     * @param string $class The prefixed version of the class name.
     *
     * @return string The non prefixed version of the class name.
     */
    private function getClassName($class)
    {
        return str_replace('tad_DI52_', '', $class);
    }
}
