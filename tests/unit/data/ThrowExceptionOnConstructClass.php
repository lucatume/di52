<?php

namespace unit\data;

class ThrowExceptionOnConstructClass
{
    public function __construct()
    {
        throw new \Exception('!!! Exception while building class ' . __CLASS__);
    }
}
