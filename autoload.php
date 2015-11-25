<?php

function __tad_DI52_get_file_path($class)
{
    if (strpos($class, 'tad_DI52_') !== 0) {
        return '';
    }
    $file_name = str_replace('tad_DI52_', '', $class);
    $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $file_name . '.php';

    return $file;
}

function __tad_DI52_autoload($class)
{
    $file = __tad_DI52_get_file_path($class);

    if (file_exists($file)) {
        include_once $file;
    }
}

spl_autoload_register('__tad_DI52_autoload');

if (!class_exists('DI52')) {
    class DI52 extends tad_DI52_Container
    {
    }
}
