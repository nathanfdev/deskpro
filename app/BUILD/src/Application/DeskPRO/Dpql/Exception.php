<?php

namespace Application\DeskPRO\Dpql;

use DeskPRO\Bundle\AppBundle\Exception\HelpdeskInstanceExceptionInterface;

/**
 * The exception for an error that occurs when compiling, preparing, or
 * executing a DPQL statement.
 */
class Exception extends \Exception implements HelpdeskInstanceExceptionInterface
{
}
