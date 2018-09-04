<?php

namespace mrcrmn\Container;

use mrcrmn\Container\Container;
use mrcrmn\Container\Reflector;

class Resolver
{
    /**
     * The classname.
     *
     * @var string
     */
    protected $class;

    /**
     * The container instance.
     *
     * @var \mrcrmn\Container\Container
     */
    protected $container;

    /**
     * The constructor.
     *
     * @param string|object $class
     * @param string $method
     */
    public function __construct($class, Container $container)
    {
        $this->class = Reflector::getClassName($class);
        $this->container = $container;
    }

    /**
     * Resolves the method arguments for a given class
     *
     * @param string|object $class
     * @param string $method
     * @return array
     */
    public static function getArguments($class, $method, Container $container)
    {
        return (new static($class, $container))->resolveMethod($method);
    }

    /**
     * Resolves the arguments for a function call.
     *
     * @param string $class
     * @return array
     */
    protected function resolveMethod($method) {
        return array_map(function($argument) {
            return $this->container->get($argument);
        }, $this->reflectMethod($this->class, $method));
    }

    /**
     * Resolves single arguments or binding from the container.
     *
     * @param Collection $arguments
     * @return array
     */
    protected function reflectMethod($class, $method)
    {
        return Reflector::reflectMethodOnClass($class, $method);
    }
}