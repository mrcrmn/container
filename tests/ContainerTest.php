<?php

namespace mrcrmn\Container\Tests;

use PHPUnit\Framework\TestCase;
use mrcrmn\Container\Container;
use Psr\Container\NotFoundExceptionInterface;
use mrcrmn\Container\Exceptions\MissingEntityException;
use mrcrmn\Container\Exceptions\EntityAlreadyExistsException;
use mrcrmn\Container\Exceptions\DifferentTypeExcpectedException;


class Testing {
    public $var = true;
}

class NoTypeHint {
    public $testing;

    public function __construct($test)
    {
        $this->testing = $test;
    }
}

class Testing2 {
    public $testing;

    public function __construct(Testing $testing)
    {
        $this->testing = $testing;
    }

    public function callMe($argument)
    {
        return $argument;
    }

    public function throwsError($whatAmI)
    {
        return $whatAmI;
    }

}

class ContainerTest extends TestCase
{

    public function test_it_can_set_objects()
    {
        $container = new Container();
        $container->set(Testing::class, new Testing);

        $resolved = $container->get(Testing::class);

        $this->assertTrue($resolved->var);
    }

    public function test_it_expects_an_exception_if_we_try_to_resolve_something_that_isnt_contained()
    {
        $this->expectException(NotFoundExceptionInterface::class);
        $container = new Container();
        $container->get('not_existing');
    }

    public function test_it_can_give_objects_via_alias()
    {
        $container = new Container();
        $container->set(Testing::class, new Testing, 'test');

        $resolved = $container->get('test');

        $this->assertTrue($resolved->var);
    }

    public function test_it_can_get_dependencies_even_if_the_object_isnt_typehinted_but_the_var_name_still_does_exist_as_an_alias()
    {
        $container = new Container();
        $container->set(Testing::class, new Testing, 'test');

        $object = $container->make(NoTypeHint::class);

        $this->assertTrue($object->testing->var);
    }

    public function test_the_bound_argument_can_be_set_via_a_closure()
    {
        $container = new Container();
        $container->set(Testing::class, function($container) {
            return new Testing();
        }, 'test');

        $resolved = $container->get('test');

        $this->assertTrue($resolved->var);
    }

    public function test_it_can_instanciate_objects_with_arguments_resolved_in_the_container()
    {
        $container = new Container();
        $container->set(Testing::class, new Testing);

        $object = $container->make(Testing2::class);

        $this->assertTrue($object->testing->var);
    }

    public function test_binding_something_that_already_exists_throws_an_error()
    {
        $this->expectException(EntityAlreadyExistsException::class);

        $container = new Container();
        $container->set(Testing::class, new Testing, 'test');
        $container->argument('test', 'this shouldnt work');
    }

    public function test_it_can_call_methods_on_an_object()
    {
        $container = new Container();
        $container->set(Testing::class, new Testing);
        $container->argument('argument', true);

        $object = $container->make(Testing2::class);

        $this->assertTrue(
            $container->call($object, 'callMe'/* Maybe */)
        );
    }

    public function test_trying_to_get_a_dependency_not_in_the_container_throws_an_error()
    {
        $this->expectException(MissingEntityException::class);

        $container = new Container();
        $container->set(Testing::class, new Testing);
        $container->argument('argument', true);

        $object = $container->make(Testing2::class);
        $container->call($object, 'throwsError');
    }
}