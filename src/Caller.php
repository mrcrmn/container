<?php

namespace mrcrmn\Container;

class Caller
{
    /**
     * The class which holds the callable method.
     *
     * @var string
     */
    protected $class;

    /**
     * The name of the method or function to call.
     *
     * @var string
     */
    protected $method;

    /**
     * The methods arguments.
     *
     * @var array
     */
    protected $arguments = array();

    /**
     * The constructor.
     *
     * @param string $method
     * @param array $arguments
     * @param string|null $class
     */
    public function __construct($method, $arguments = array(), $class = null)
    {
        $this->method = $method;
        $this->arguments = $arguments;
        $this->class = $class;
    }

    /**
     * Calls a method on a class.
     *
     * @return mixed
     */
    public function callMethod()
    {
        return call_user_func_array(array($this->class, $this->method), $this->arguments);
    }

    /**
     * Calls a function.
     *
     * @return mixed
     */
    public function callFunction()
    {
        return call_user_func_array($this->method, $this->arguments);
    }

    /**
     * Determines whether we are in a class or function context and calls the equivalent.
     *
     * @return mixed
     */
    public function callMethodOrFunction()
    {
        if (is_null($this->class)) {
            return $this->callFunction();
        }

        return $this->callMethod();
    }

    /**
     * Automatically calls a method on a class or function.
     *
     * @param string|object $class
     * @param string|null|array $method
     * @param array $arguments
     * @return mixed
     */
    public static function call($class, $method = null, $arguments = array())
    {
        if (is_null($method)) {
            return (new static($class, $arguments))->callFunction();
        }

        return (new static($method, $arguments, $class))->callMethod();
    }
}