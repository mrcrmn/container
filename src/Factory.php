<?php

namespace mrcrmn\Container;

use mrcrmn\Container\Reflector;

class Factory
{
    /**
     * Creates a new object.
     *
     * @param string|object $class
     * @param array $arguments
     * @return object
     */
    public static function create($class, $arguments)
    {
        return (new Reflector($class))->newInstanceArgs($arguments);
    }
}