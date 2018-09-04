<?php

namespace mrcrmn\Container;

use mrcrmn\Container\Reflector;
use Psr\Container\ContainerInterface;

class Resolver
{
    /**
     * The class or function name.
     *
     * @var string
     */
    protected $class;

    /**
     * The container instance.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The constructor.
     *
     * @param string|object $class
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct($class, ContainerInterface $container)
    {
        $this->class = Reflector::getClassName($class);
        $this->container = $container;
    }

    /**
     * Resolves the method arguments for a given class
     *
     * @param string|object $class
     * @param string $method
     * @param \Psr\Container\ContainerInterface $container
     * @return array
     */
    public static function getArguments($class, $method = null, ContainerInterface $container)
    {
        return (new static($class, $container))->resolveMethod($method);
    }

    /**
     * Resolves the arguments for a method.
     *
     * @param string $method
     * @return array
     */
    protected function resolveMethod($method = null) {
        return array_map(function($argument) {
            return $this->container->get($argument);
        }, $this->reflectMethod($method));
    }

    /**
     * Reflects a method on this class and returns the arguments.
     *
     * @param string $method
     * @return array
     */
    protected function reflectMethod($method = null)
    {
        if (is_null($method)) {
            return Reflector::reflectFunction($this->class);
        }

        return Reflector::reflectMethodOnClass($this->class, $method);
    }
}