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
    public function bind($id, $object, $alias = null)
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
        return new $class(...$this->resolveArguments($class, '__construct'));
    }

    /**
     * Resolves the arguments for a function call.
     *
     * @param string $class
     * @return array
     */
    protected function resolveArguments($class, $method) {
        return $this->reflectMethod((new Reflector($class))->reflect($method));
    }

    /**
     * Calls a function on an object or class.
     *
     * @param object|string $object
     * @param string $method
     * @return mixed
     */
    public function call($object, $method)
    {
        return call_user_func_array(array($object, $method), $this->resolveArguments($object, $method));
    }

    /**
     * Resolves single arguments or binding from the container.
     *
     * @param Collection $arguments
     * @return array
     */
    protected function reflectMethod($arguments)
    {
        return array_map(function($argument) {
            if (! $this->has($argument)) {
                throw new MissingEntityException("Can't create Object. '{$argument}' is missing in the Container.");
            }
            return $this->get($argument);
        }, $arguments);
    }
}