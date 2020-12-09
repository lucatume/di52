<?php
/**
 * Registers the autoload function for the library.
 * The function will take care of redirecting calls to the `tad_DI52_`, non-namespaced, class format to the namespaced
 * classes.
 */

use lucatume\DI52\Autoloader;

require_once __DIR__ . '/src/Autoloader.php';

spl_autoload_register(new Autoloader());
