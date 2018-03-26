<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Form\Error;

use DeskPRO\Bundle\AppBundle\Form\Error\ExceptionErrorCodeFactory;
use DeskPRO\Bundle\AppBundle\Form\Error\ValidatorErrorCodeFactory;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Form\Error\ErrorCodeFactory
 */
class ErrorCodeFactorySpec extends ObjectBehavior
{
    public function it_uses_exception_factory_for_validation(
        ValidatorErrorCodeFactory $validator_error_code_factory,
        ExceptionErrorCodeFactory $exception_error_code_Factory
    ) {
        $e = new \Exception();

        $this->beConstructedWith($exception_error_code_Factory, $validator_error_code_factory);

        $exception_error_code_Factory->getExceptionErrorCode($e)->willReturn('bar');

        $this->getErrorCodeForException($e)->shouldReturn('bar');
    }

    public function it_uses_validator_factory_for_validation(
        ValidatorErrorCodeFactory $validator_error_code_factory,
        ExceptionErrorCodeFactory $exception_error_code_Factory,
        ConstraintViolation $constraint_violation
    ) {
        $this->beConstructedWith($exception_error_code_Factory, $validator_error_code_factory);

        $validator_error_code_factory->getConstraintErrorCode($constraint_violation)->willReturn('bar');

        $this->getErrorCodeForConstraintViolation($constraint_violation)->shouldReturn('bar');
    }

    public function it_uses_validator_factory_for_form_errors(
        ValidatorErrorCodeFactory $validator_error_code_factory,
        ExceptionErrorCodeFactory $exception_error_code_Factory,
        FormError $form_error
    ) {
        $this->beConstructedWith($exception_error_code_Factory, $validator_error_code_factory);

        $validator_error_code_factory->getFormErrorCode($form_error)->willReturn('bar');

        $this->getErrorCodeForFormError($form_error)->shouldReturn('bar');
    }
}
