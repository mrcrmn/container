<?php

namespace mrcrmn\Container;

use Closure;
use mrcrmn\Container\Reflector;
use mrcrmn\Collection\Collection;
use Psr\Container\ContainerInterface;
use mrcrmn\Container\Exceptions\MissingEntityException;
use mrcrmn\Container\Exceptions\EntityAlreadyExistsException;
use mrcrmn\Container\Exceptions\DifferentTypeExcpectedException;

class Container implements ContainerInterface
{
    /**
     * Array of all contained bindings.
     *
     * @var Collection
     */
    protected $bindings;

    /**
     * Array of all aliased bindings.
     *
     * @var Collection
     */
    protected $aliases;

    /**
     * The Constructor creates the bindings and alias collections.
     */
    public function __construct()
    {
        $this->bindings = new Collection();
        $this->aliases = new Collection();
    }

    /**
     * Protects bindings from being overwritten.
     *
     * @param string $id
     * @return void
     */
    protected function guardBindings($id) {
        if ($this->has($id)) {
            throw new EntityAlreadyExistsException("Entity does already exists in the Container.");
        }
    }

    /**
     * Checks if the given object is a function and calls it.
     * Else it just returns the given value.
     *
     * @param Closure|object $object
     * @return obejct
     */
    protected function getObject($object, $id)
    {
        if ($object instanceof Closure) {
            $object = $object($this);
        }

        if (! $object instanceof $id) {
            throw new DifferentTypeExcpectedException("The given object is not of type '{$id}'.");
        }

        return $object;
    }

    /**
     * Binds an object to the core.
     *
     * @param string $id
     * @param object $object
     * @return $this
     */
    public function set($id, $object, $alias = null)
    {
        $this->guardBindings($id);
        $this->bindings->set($id, $this->getObject($object, $id));

        if (isset($alias)) {
            $this->aliases->set($alias, $id);
        }

        return $this;
    }

    /**
     * Alias for the setter.
     *
     * @param string $id
     * @param object|Closure $object
     * @param string $alias
     * @return $this
     */
    public function entity($id, $object, $alias = null)
    {
        return $this->set($id, $object, $alias);
    }

    /**
     * Adds a single argument to the core.
     *
     * @param string $id
     * @param mixed $value
     * @return $this
     */
    public function argument($id, $value)
    {
        $this->guardBindings($id);
        $this->bindings->set($id, $value);

        return $this;
    }

    /**
     * Checks if an entity or argument exists.
     *
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return $this->bindings->has($id) || $this->aliases->has($id);
    }

    /**
     * Gets an object from the container.
     *
     * @param string $id
     * @return object|mixed
     */
    public function get($id)
    {
        // Throw an exception if the id doesn't exist in the container.
        if (! $this->has($id)) {
            throw new MissingEntityException("'{$id}' is missing from the container.");
        }

        // We check if the id is a key in the aliases.
        // If so we use the resolved alias value as a key for
        // the main container.
        return $this->aliases->has($id)
             ? $this->bindings->get($this->aliases->get($id)) 
             : $this->bindings->get($id);
    }

    /**
     * Alias for the getter.
     *
     * @param string $id
     * @return object|mixed
     */
    public function resolve($id)
    {
        return $this->get($id);
    }

    /**
     * Automatically constructs an object with parameters contained in the core.
     *
     * @param string $class
     * @return object
     */
    public function make($class)
    {
        $resolved = $this->resolveObjectsForConstructor(
            (new Reflector($class))->reflect()
        );

        return new $class(...$resolved);
    }

    public function call($object, $method)
    {
        $resolved = $this->resolveObjectsForConstructor(
            (new Reflector(get_class($object)))->reflect($method)
        );

        return $object->{$method}(...$resolved);
    }

    /**
     * Resolves single arguments or entity objects from the container.
     *
     * @param Collection $arguments
     * @return array
     */
    protected function resolveObjectsForConstructor(Collection $arguments)
    {
        return $arguments->map(function($argument) {
            if ($this->has($argument)) {
                return $this->resolve($argument);
            }
            throw new MissingEntityException("Can't create Object. '{$argument}' is missing in the Container.");
        })->toArray();
    }
}