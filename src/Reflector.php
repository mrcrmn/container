<?php

namespace mrcrmn\Container;

use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;
use mrcrmn\Container\Container;
use mrcrmn\Collection\Collection;
use mrcrmn\Container\Exceptions\InvalidMethodException;

class Reflector extends ReflectionClass
{
    /**
     * The Constructor also accepts objects.
     *
     * @param string|object $class
     */
    public function __construct($class)
    {
        parent::__construct(static::getClassName($class));
    }

    /**
     * Static call to return a method reflection.
     *
     * @param string|object $class
     * @param string $method
     * @return array
     */
    public static function reflectMethodOnClass($class, $method)
    {
        return (new static($class))->reflect($method);
    }

    /**
     * Gets the functions parameters.
     *
     * @param string $function
     * @return array
     */
    public static function reflectFunction($function)
    {
        return array_map(array(static::class, 'reflectParameter'), (new ReflectionFunction($function))->getParameters());
    }

    /**
     * Returns the class name from an object or returns the argument if its a string.
     *
     * @param object|string $class
     * @return string
     */
    public static function getClassName($class)
    {
        if (! is_string($class)) {
            $class = get_class($class);
        }

        return $class;
    }

    /**
     * Throws an error if the method to reflect doesn't exist.
     *
     * @param string $method
     * @return void
     */
    protected function guardMethod($method)
    {
        if (! $this->hasMethod($method)) {
            throw new InvalidMethodException("Method: '{$method}' doesn't exists on '{$this->getName()}'");
        }
    }

    /**
     * Gets the parameters of the method on this class.
     *
     * @param string $method
     * @return \ReflectionParameter[]
     */
    protected function getParametersForMethod($method)
    {
        return $this->getMethod($method)->getParameters();
    }

    /**
     * Returns the parameter name.
     *
     * @return string
     */
    protected static function reflectParameter(ReflectionParameter $parameter)
    {
        // We first check if the parameter has a type hinted class,
        // if so we return the class name, else just the name.
        return ! is_null($parameter->getClass())
               ? $parameter->getClass()->name
               : $parameter->name;
    }
    
    /**
     * Returns an array the methods parameter names, or type hinted class names.
     *
     * @return array
     */
    public function reflect($method = '__construct')
    {
        // Throw an exception if the method doesn't exists.
        $this->guardMethod($method);

        return array_map(array(static::class, 'reflectParameter'), $this->getParametersForMethod($method));
    }
}
