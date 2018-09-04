<?php

namespace mrcrmn\Container;

use Closure;
use mrcrmn\Container\Caller;
use mrcrmn\Container\Factory;
use mrcrmn\Container\Resolver;
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
     * Also binds itself to the container.
     */
    public function __construct($alias = null)
    {
        $this->bindings = new Collection();
        $this->aliases = new Collection();

        $this->bindSelf($alias);
    }

    /**
     * Protects bindings from being overwritten.
     *
     * @param string $id
     * @return mixed
     */
    protected function guardBindings($id) {
        if ($this->has($id)) {
            throw new EntityAlreadyExistsException("Entity does already exists in the Container.");
        }
    }

    /**
     * Binds itself to the container using the given alias.
     *
     * @param string $alias
     * @return $this
     */
    protected function bindSelf($alias = null)
    {
        return $this->set(static::class, $this, $alias);
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
     * Binds an object to the container.
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
            $this->addAlias($alias, $id);
        }

        return $this;
    }

    /**
     * Sets an alias.
     *
     * @param string $alias
     * @param string $id
     * @return void
     */
    public function addAlias($alias, $id)
    {
        $this->aliases->set($alias, $id);
    }

    /**
     * Alias for the setter.
     *
     * @param string $id
     * @param object|Closure $object
     * @param string $alias
     * @return $this
     */
    public function bind($id, $object, $alias = null)
    {
        return $this->set($id, $object, $alias);
    }

    /**
     * Adds a single argument to the Container.
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