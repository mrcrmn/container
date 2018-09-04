<?php

namespace mrcrmn\Container\Traits;

use mrcrmn\Container\Resolver;

trait ResolvesArguments
{
    /**
     * Resolves arguments for a given method or function.
     * 
     * if $method is null, we assume that the user
     * wants to resolve arguments for a function
     *
     * @param string|object $class
     * @param string|null $method
     * @return array
     */
    protected function resolveArguments($class, $method = null)
    {
        return Resolver::getArguments($class, $method, $this);
    }
}
