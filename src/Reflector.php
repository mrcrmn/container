<?php

namespace mrcrmn\Container;

use ReflectionClass;
use mrcrmn\Container\Container;
use mrcrmn\Collection\Collection;
use mrcrmn\Container\Exceptions\InvalidMethodException;

class Reflector extends ReflectionClass
{
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

        $parameters = new Collection($method->getParameters());

        return $parameters->map(function($parameter) {
            $class = $parameter->getClass();
            return isset($class->name) ? $class->name : $parameter->name;
        });
    }
}
