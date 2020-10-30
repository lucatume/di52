<?php
require_once dirname(dirname(__FILE__)) . '/vendor/autoload_52.php';
require_once dirname(__FILE__) . '/data/test-classes.php';
require_once dirname(__FILE__) . '/data/test-car-classes.php';
if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
    require_once dirname(__FILE__) . '/data/namespaced-test-classes.php';
}
require_once dirname(__FILE__) . '/data/test-providers.php';

function assertMatchesSnapshots($actual){
	foreach ( debug_backtrace( true) as $entry){
		if(
			isset($entry['class'], $entry['function'], $entry['object'])
		   && $entry['object'] instanceof PHPUnit_Framework_TestCase
		) {
			$testCase   = $entry['class'];
			/** @var PHPUnit_Framework_TestCase $tc */
			$tc                 = $entry['object'];
			$testMethod = $tc->getName(true);
			$testCaseReflection = new ReflectionClass(get_class($tc));
			$root = dirname($testCaseReflection->getFileName());
			break;
		}
	}

	if ( ! isset( $testCase ) ) {
		throw new \RuntimeException( 'Could not determine test case from trace.' );
	}

	static $counts;
	$counts = $counts === null ? array() : $counts;

	$counts["{$testCase}-{$testMethod}"] = isset( $counts["{$testCase}-{$testMethod}"] ) ?
		$counts["{$testCase}-{$testMethod}"] + 1
		: 1;
	$count = $counts["{$testCase}-{$testMethod}"];
	$snapshot = $root . "/__snapshots__/{$testCase}-{$testMethod}.snapshot-{$count}";

	if (!is_file($snapshot)) {
		if ( ! is_dir( dirname( $snapshot ) ) && ! mkdir( dirname( $snapshot ), 0777,
				true ) && ! is_dir( dirname( $snapshot ) ) ) {
			throw new RuntimeException( 'Could not create snapshot directory.' );
		}
		if(!file_put_contents($snapshot,$actual)){
			throw new RuntimeException('Could not write snapshot contents to file.');
		}
		PHPUnit_Framework_Assert::markTestSkipped('Snapshot updated');
	} else {
		$expected =file_get_contents($snapshot);
		PHPUnit_Framework_Assert::assertEquals($expected, $actual,'Failed asserting that current contents match snapshot.');
	}
}
