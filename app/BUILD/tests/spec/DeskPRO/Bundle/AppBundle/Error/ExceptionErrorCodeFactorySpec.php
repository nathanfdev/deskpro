<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Form\Error;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Form\Error\ExceptionErrorCodeFactory
 */
class ExceptionErrorCodeFactorySpec extends ObjectBehavior
{
    public function it_first_checks_a_map_of_text_message_to_response_codes_for_pre_defined_codes()
    {
        $this->getExceptionErrorCode(new \Exception('Invalid JSONP callback value'))->shouldReturn('invalid_jsonp_callback');
    }

    public function it_returns_the_error_message_if_it_was_thrown_with_one()
    {
        $this->getExceptionErrorCode(new \Exception('my_error_code'))->shouldReturn('my_error_code');
    }

    public function it_gets_default_exception_error_code_if_none_defined()
    {
        $this->getExceptionErrorCode(new NewException())->shouldReturn(ErrorsCodes::EXCEPTION_FALLBACK);
    }

    public function it_uses_the_map_for_blank_message_http_kernel_exceptions(
        FlattenException $e
    ) {
        $this->getExceptionErrorCode(new BadRequestHttpException())->shouldReturn(ErrorsCodes::BAD_REQUEST);
    }

    public function it_uses_the_map_for_non_kernel_exceptions(
        FormInterface $form
    ) {
        $this->getExceptionErrorCode(new InvalidFormException($form->getWrappedObject()))->shouldReturn(ErrorsCodes::INVALID_INPUT);
    }
}

class NewException extends \Exception
{
}
