<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/data/test-classes.php';
require_once __DIR__ . '/data/test-car-classes.php';
require_once __DIR__ . '/data/namespaced-test-classes.php';
require_once __DIR__ . '/data/test-providers.php';

function assertMatchesSnapshots($actual, $prefix = null)
{
    foreach (debug_backtrace(true) as $entry) {
        if (isset($entry['class'], $entry['function'], $entry['object'])
           && $entry['object'] instanceof PHPUnit_Framework_TestCase
        ) {
            $testCase   = $entry['class'];
            $tc                 = $entry['object'];
            $testMethod = $tc->getName(true);
            $testCaseReflection = new ReflectionClass(get_class($tc));
            $root = dirname($testCaseReflection->getFileName());
            break;
        }
    }

    if (! isset($testCase)) {
        throw new \RuntimeException('Could not determine test case from trace.');
    }

    static $counts;
    $counts = $counts === null ? [] : $counts;

    $counts["{$testCase}-{$testMethod}"] = isset($counts["{$testCase}-{$testMethod}"]) ?
        $counts["{$testCase}-{$testMethod}"] + 1
        : 1;
    $count = $counts["{$testCase}-{$testMethod}"];
    $snapshot = $root . "/__snapshots__/{$testCase}-{$testMethod}.{$prefix}snapshot-{$count}";

    if (!is_file($snapshot)) {
        if (! is_dir(dirname($snapshot)) && ! mkdir(
            dirname($snapshot),
            0777,
            true
        ) && ! is_dir(dirname($snapshot))) {
            throw new RuntimeException('Could not create snapshot directory.');
        }
        if (!file_put_contents($snapshot, $actual)) {
            throw new RuntimeException('Could not write snapshot contents to file.');
        }
        PHPUnit_Framework_Assert::markTestSkipped('Snapshot updated');
    } else {
        $expected =file_get_contents($snapshot);
        PHPUnit_Framework_Assert::assertEquals($expected, $actual, 'Failed asserting that current contents match snapshot.');
    }
}
