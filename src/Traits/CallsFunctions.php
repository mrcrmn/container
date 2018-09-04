<?php

namespace mrcrmn\Container\Traits;

use mrcrmn\Container\Caller;

trait CallsFunctions
{
    /**
     * Calls resolves arguments for a method on a class an calls it.
     * 
     * If $method is null, we assume,
     * that the user wants to call a function, not a method.
     *
     * @param object|string $object
     * @param string $method
     * @return mixed
     */
    public function call($object, $method = null)
    {
        return Caller::call($object, $method, $this->resolveArguments($object, $method));
    }
}
