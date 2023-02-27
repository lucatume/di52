<?php

namespace unit\data;

class ThrowErrorOnConstructClass
{
    public function __construct()
    {
        throw new \Error('!!! Error while building class ' . __CLASS__);
    }
}
