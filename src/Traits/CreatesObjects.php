<?php

namespace mrcrmn\Container\Traits;

use mrcrmn\Container\Factory;

trait CreatesObjects
{
    /**
     * Automatically constructs an object with container bindings.
     *
     * @param string $class
     * @return object
     */
    public function make($class)
    {
        return Factory::create($class, $this->resolveArguments($class, '__construct'));
    }
}
