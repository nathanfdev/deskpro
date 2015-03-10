<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace spec\DeskPRO\Bundle\ApiBundle\Error;

use DeskPRO\Bundle\ApiBundle\Error\ExceptionErrorCodeFactory;
use DeskPRO\Bundle\ApiBundle\Error\ValidatorErrorCodeFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use DeskPRO\Bundle\ApiBundle\Error\ErrorCodeFactory;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @mixin \DeskPRO\Bundle\ApiBundle\Error\ErrorCodeFactory
 */
class ErrorCodeFactorySpec extends ObjectBehavior
{
    function it_uses_exception_factory_for_validation(
        ValidatorErrorCodeFactory $validator_error_code_factory,
        ExceptionErrorCodeFactory $exception_error_code_Factory
    )
    {
        $e = new FlattenException(new \Exception());

        $this->beConstructedWith($exception_error_code_Factory, $validator_error_code_factory);

        $exception_error_code_Factory->getExceptionErrorCode($e)->willReturn('bar');

        $this->getErrorCodeForException($e)->shouldReturn('bar');
    }

    function it_uses_validator_factory_for_validation(
        ValidatorErrorCodeFactory $validator_error_code_factory,
        ExceptionErrorCodeFactory $exception_error_code_Factory,
        ConstraintViolation $constraint_violation
    )
    {
        $this->beConstructedWith($exception_error_code_Factory, $validator_error_code_factory);

        $validator_error_code_factory->getConstraintErrorCode($constraint_violation)->willReturn('bar');

        $this->getErrorCodeForConstraintViolation($constraint_violation)->shouldReturn('bar');
    }
}
