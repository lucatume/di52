<?php

namespace unit\data;

class ThrowParseErrorOnConstructClass
{
    public function __construct()
    {
        throw new \ParseError('!!! Parse error while building class ' . __CLASS__);
    }
}
