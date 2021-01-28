<?php

class Engine
{
}

class Car
{
    public function __construct($one, array $two = [], Engine $three = null, $four=23)
    {
    }
}

$epochs = 100000;

$m = new ReflectionMethod(Car::class, '__construct');

//// OO way.
$epoch = 0;
echo str_pad("\nOO ...",20);
$oostart = microtime(true);
while ($epoch++ < $epochs) {
    foreach ($m->getParameters() as $i => $parameter) {
        // Parameter #0 [ <optional> $one = 23 ]
        // Parameter #1 [ <optional> array $two = Array ]
        // Parameter #2 [ <optional> Engine or NULL $three = NULL ]
        $string = $parameter->__toString();
        $s = trim(str_replace('Parameter #' . $i, '', $string), '[ ]');
        $frags = explode(' ', $s);
        $equalsIndex = array_search('=', $frags);

        $isOptional = $frags[0] === '<optional>';
        $paramData = [
            'class' => strpos($frags[1], '$') === 0 ? null : $frags[1],
            'isOptional' => $isOptional,
            'defaultValue' => $isOptional ? $parameter->getDefaultValue() : null,
        ];
    }
}
$ootime = microtime(true) - $oostart;
echo $ootime;
////

//// toString way.
$epoch = 0;
echo str_pad("\n__toString ...",20);
$toStringStart = microtime(true);
while ($epoch++ < $epochs) {
    foreach ($m->getParameters() as $parameter) {
        $paramData = [
            'class' => $parameter->getClass() ?: null,
            'isOptional' => $parameter->isOptional(),
            'defaultValue' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
        ];
    }
}
$toStringtime = microtime(true) - $toStringStart;
echo $toStringtime;
////
echo "\nOO_time / TS_time = " . ($ootime / $toStringtime);

