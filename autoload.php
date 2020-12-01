<?php
/**
 * Registers the autoload function for the library.
 * The function will take care of redirecting calls to the `tad_DI52_`, non-namespaced, class format to the namespaced classes.
 */

namespace lucatume\DI52;

if ( function_exists( 'autoloader' ) ) {
	return;
}

/**
 * Locates and load a library class in its namespaced or non-namespaced format.
 *
 * @param string $class The fully qualified name of the class to try and locate.
 *
 * @return bool Whether the class was located and loaded or not.
 */
function autoloader( $class ) {
	static $loadedClasses;

	if ( count( (array) $loadedClasses ) === 3 ) {
		return false;
	}

	$alias = false;
	if ( strpos( $class, '\\lucatume\\DI52\\' ) !== false ) {
		$className = str_replace( 'tad_DI52_', '', $class );
	} elseif ( strpos( $class, 'tad_DI52_' ) !== false ) {
		$className = str_replace( 'tad_DI52_', '', $class );
		$alias = true;
	} else{
		return false;
	}

	$path = __DIR__ . '/src/' . $className . '.php';

	if ( is_file( $path ) ) {
		/** @noinspection PhpIncludeInspection */
		require_once $path;
		$loadedClass     = '\\lucatume\\DI52\\' . $className;
		$loadedClasses[] = array_merge( (array) $loadedClasses, [ $loadedClass ] );
		if ( $alias ) {
			class_alias( $loadedClass, $class );
		}

		return true;
	}

	return false;
}

spl_autoload_register( 'lucatume\DI52\autoloader' );
