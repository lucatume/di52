#! /usr/bin/env php
<?php
/**
 * An entry script to run a specific suite and test.
 * Builds on the kocsismate/di-container-benchmarks package.
 */
declare(strict_types=1);

require_once __DIR__ . "/benchmark/app/bootstrap.php";

use DiContainerBenchmarks\Test\TestRunner;

if (isset($_GET["opcache"])) {
	echo "<pre>";
	var_dump(opcache_get_status());
	exit;
}

if (isset($_GET["clear"])) {
	opcache_reset();
	exit;
}

list( $testSuiteNumber, $testCaseNumber ) = explode( '.', $argv[1] );

$testRunner = new TestRunner();

echo $testRunner->runTest((int)$testSuiteNumber, (int)$testCaseNumber, 'di52')->toJson();
