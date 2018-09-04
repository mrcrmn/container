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

class ContainerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }

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

    public function test_the_bound_argument_can_be_set_via_a_closure()
    {
        $container = new Container();
        $container->set(Testing::class, function($container) {
            return new Testing();
        }, 'test');

        $resolved = $container->get('test');

        $this->assertTrue($resolved->var);
    }
}