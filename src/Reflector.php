<?php

namespace Nhance\Core;

use ReflectionClass;
use mrcrmn\Container\Container;
use mrcrmn\Collection\Collection;

class Reflector
{
    /**
     * The class name to reflect.
     *
     * @var string
     */
    protected $class;

    /**
     * The constructor.
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * Gets the name of parameters and the type-hinted class name.
     *
     * @return Collection
     */
    public function reflect($method = '__construct')
    {
        $reflector = new ReflectionClass($this->class);

        if (! $reflector->hasMethod($method)) {
            return new Collection();
        }

        $method = $reflector->getMethod($method);

        $parameters = new Collection($method->getParameters());

        return $parameters->map(function($parameter) {
            $class = $parameter->getClass();
            return isset($class->name) ? $class->name : $parameter->name;
        });
    }
}
