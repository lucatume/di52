<?php
/**
 * ${CARET}
 *
 * @since   TBD
 *
 * @package lucatume\DI52\Builders
 */


namespace lucatume\DI52\Builders;

use lucatume\DI52\ContainerException;
use ReflectionParameter;

class Parameter
{
    /**
     * @var array
     */
    protected $data;
    /**
     * @var mixed|string|null
     */
    protected $type;
    /**
     * @var bool
     */
    protected $isOptional;
    /**
     * @var mixed|null
     */
    protected $defaultValue;
    protected $isClass;

    private static $nonClassTypes = [
        'string',
        'int',
        'bool',
        'float',
        'double',
        'array',
        'resource',
        'callable',
        'iterable',
    ];
    private static $conversionMap = [
        'integer' => 'int',
        'boolean' => 'bool',
        'double' => 'float'
    ];
    protected $name;

    public function __construct($index, ReflectionParameter $reflectionParameter)
    {
        $string = $reflectionParameter->__toString();
        $s = trim(str_replace('Parameter #' . $index, '', $string), '[ ]');
        $frags = explode(' ', $s);

        $this->name = $reflectionParameter->name;
        $this->type = strpos($frags[1], '$') === 0 ? null : $frags[1];
        // PHP 8.0 nullables.
        $this->type = str_replace('?', '', $this->type) ;
        if (isset(static::$conversionMap[$this->type])) {
            $this->type = static::$conversionMap[$this->type];
        }
        $this->isClass = $this->type && !in_array($this->type, static::$nonClassTypes, true);
        $this->isOptional = $frags[0] === '<optional>';
        $this->defaultValue = $this->isOptional ? $reflectionParameter->getDefaultValue() : null;
    }

    public function getData()
    {
        return [
            'type' => $this->type,
            'isOptional' => $this->isOptional,
            'defaultValue' => $this->defaultValue
        ];
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getClass()
    {
        return $this->isClass ? $this->type : null;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDefaultValueOrFail()
    {
        if (!$this->isOptional) {
            throw new ContainerException(
                sprintf(
                    'Parameter $%s is not optional and is not type-hinted: auto-wiring is not magic.',
                    $this->name
                )
            );
        }
        return $this->defaultValue;
    }
}
