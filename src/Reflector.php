<?php

namespace mrcrmn\Container;

use ReflectionClass;
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
     * Gets the name of parameters and the type-hinted class name.
     *
     * @return Collection
     */
    public function reflect($method = '__construct')
    {
        if (! $this->hasMethod($method)) {
            throw new InvalidMethodException("Method: '{$method}' doesn't exists on '{$this->getName()}'");
        }

        $method = $this->getMethod($method);

        return array_map(function($parameter) {
            $class = $parameter->getClass();
            return isset($class->name) ? $class->name : $parameter->name;
        }, $method->getParameters());
    }
}
