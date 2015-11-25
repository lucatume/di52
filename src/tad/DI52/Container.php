<?php


class tad_DI52_Container implements ArrayAccess
{

    /**
     * @var tad_DI52_Var[]
     */
    protected $vars = array();

    /**
     * @var tad_DI52_Ctor[]
     */
    protected $ctors = array();

    public function set_var($alias, $value = null)
    {
        if (!isset($this->vars[$alias])) {
            $this->vars[$alias] = tad_DI52_Var::create($value);
        }

        return $this;
    }

    public function get_var($alias)
    {
        $this->assert_var_alias($alias);

        return $this->vars[$alias]->get_value();
    }

    /**
     * Sets a class instance constructor with optional arguments.
     *
     * @param $alias
     * @param $class_and_method
     * @param null $arg_one One or more optional arguments that should be passed to the class constructor.
     *
     * @return tad_DI52_Ctor
     */
    public function set_ctor($alias, $class_and_method, $arg_one = null)
    {
        if (!isset($this->ctors[$alias])) {
            $func_args = func_get_args();
            $args = array_splice($func_args, 2);

            return $this->ctors[$alias] = tad_DI52_Ctor::create($class_and_method, $args, $this);
        }

        return false;
    }

    /**
     * Builds and returns a class instance.
     *
     * @param $alias
     *
     * @return mixed|object
     */
    public function make($alias)
    {
        $this->assert_ctor_alias($alias);

        $ctor = $this->ctors[$alias];

        $instance = $ctor->get_object_instance();

        return $instance;
    }

    /**
     * @param $alias
     */
    protected function assert_ctor_alias($alias)
    {
        if (!array_key_exists($alias, $this->ctors)) {
            throw new InvalidArgumentException("No constructor with the $alias alias is registered");
        }
    }

    /**
     * @param $alias
     */
    protected function assert_var_alias($alias)
    {
        if (!array_key_exists($alias, $this->vars)) {
            throw new InvalidArgumentException("No variable with the $alias alias is registered");
        }
    }

    /**
     * Sets a singleton (shared) object instance to be returned each time requested.
     *
     * @param string $alias The pretty name the shared instance will go by.
     * @param string $class_and_method The fully qualified name of the class to instance and an optional double colon
     *                          separated static constructor method.
     *
     * @param null $arg_one One or more optional parameters to use in the object construction.
     *
     * @return $this
     */
    public function set_shared($alias, $class_and_method, $arg_one = null)
    {
        if (!isset($this->ctors[$alias])) {
            $func_args = func_get_args();
            $args = array_splice($func_args, 2);
            $this->ctors[$alias] = tad_DI52_Singleton::create($class_and_method, $args, $this);
        }

        return $this;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return isset($this->ctors[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        if (isset($this->ctors[$offset])) {
            return $this->make($offset);
        } else {
            return $this->get_var($offset);
        }
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $_value = is_array($value) ? $value : array($value);
        $class_and_method = $_value[0];
        if (strpos($class_and_method, '::') || class_exists($class_and_method)) {
            $args = array_merge(array($offset), $_value);
            call_user_func_array(array($this, 'set_shared'), $args);
        } else {
            $this->set_var($offset, $value);
        }
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (isset($this->ctors[$offset])) {
            unset($this->ctors[$offset]);
        } else {
            unset($this->vars[$offset]);
        }
    }
}