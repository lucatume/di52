<?php

class VersionTest extends PHPUnit_Framework_TestCase
{
    /**
     * version compare on Travis
     */
    public function test_version_compare_on_travis()
    {
        $this->assertNotEquals(0, version_compare(phpversion(), '5.2.17'));
    }
}
