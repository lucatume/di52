<?php
/**
 * Registers the autoload function for the library.
 * The function will take care of redirecting calls to the `tad_DI52_`, non-namespaced, class format to the namespaced
 * classes.
 */

//use lucatume\DI52\Autoloader;
$aliases = [
    [ 'lucatume\DI52\Container', 'tad_DI52_Container' ],
    [ 'lucatume\DI52\ServiceProvider', 'tad_DI52_ServiceProvider' ],
    [ 'lucatume\DI52\ProtectedValue', 'tad_DI52_ProtectedValue' ]
];
foreach ($aliases as list( $class, $alias )) {
    if (! class_exists($alias)) {
        class_alias($class, $alias);
    }
}

//require_once __DIR__ . '/src/Autoloader.php';

//spl_autoload_register(new Autoloader());
