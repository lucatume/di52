<?php
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    require_once dirname(__DIR__) . '/vendor/autoload_52.php';
} else {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}
include_once dirname(__FILE__) . '/data/test-classes.php';
include_once dirname(__FILE__) . '/data/namespaced-test-classes.php';
include_once dirname(__FILE__) . '/data/test-providers.php';
