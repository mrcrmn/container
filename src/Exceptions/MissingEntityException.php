<?php

namespace mrcrmn\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class MissingEntityException extends Exception implements NotFoundExceptionInterface
{

}