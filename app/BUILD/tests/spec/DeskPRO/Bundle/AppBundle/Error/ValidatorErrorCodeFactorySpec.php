<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Form\Error;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Form\Error\ValidatorErrorCodeFactory
 */
class ValidatorErrorCodeFactorySpec extends ObjectBehavior
{
    public function it_maps_certain_constraints_to_an_error_code(
        ConstraintViolation $violation
    ) {
        $violation->getCause()->willReturn(null);
        $violation->getMessage()->willReturn(null);

        $violation->getConstraint()->willReturn(new NotNull());
        $this->getConstraintErrorCode($violation)->shouldReturn(ErrorsCodes::NOT_NULL);

        $violation->getConstraint()->willReturn(new NotBlank());
        $this->getConstraintErrorCode($violation)->shouldReturn(ErrorsCodes::NOT_BLANK);

        $violation->getConstraint()->willReturn(new Length(['min' => 5]));
        $this->getConstraintErrorCode($violation)->shouldReturn(ErrorsCodes::LENGTH_TOO_SHORT);

        $violation->getConstraint()->willReturn(new Type(['type' => 'null']));
        $this->getConstraintErrorCode($violation)->shouldReturn(ErrorsCodes::INVALID_DATA_TYPE);

        $violation->getConstraint()->willReturn(new Valid());
        $this->getConstraintErrorCode($violation)->shouldReturn(ErrorsCodes::INVALID_INPUT);
    }

    public function it_takes_the_constraint_violation_and_returns_the_exception_message_if_no_constraint(
        ConstraintViolation $violation
    ) {
        $violation->getCause()->willReturn(null);
        $violation->getConstraint()->willReturn(null);

        // right now this is the same as the message
        $violation->getMessage()->willReturn('error_code');

        $this->getConstraintErrorCode($violation)->shouldReturn('error_code');
    }

    public function it_uses_fallback_if_violation_has_no_constraint_and_an_empty_message_property(
        ConstraintViolation $violation
    ) {
        $violation->getCause()->willReturn(null);
        $violation->getConstraint()->willReturn(null);
        $violation->getMessage()->willReturn(null);

        $this->getConstraintErrorCode($violation)->shouldReturn(ErrorsCodes::CONSTRAINT_FALLBACK);
    }

    public function it_treats_form_transformation_exception_as_a_type_error(
        FormError $fe,
        ConstraintViolation $violation,
        TransformationFailedException $exception
    ) {
        $fe->getCause()->willReturn($violation);
        $violation->getCause()->willReturn($exception);
        $violation->getMessage()->willReturn(null);

        $this->getFormErrorCode($fe)->shouldReturn(ErrorsCodes::INVALID_DATA_TYPE);
    }

    public function it_treats_transformat_form_errors_in_a_special_way(
        FormError $fe,
        ConstraintViolation $violation
    ) {
        $fe->getCause()->willReturn($violation);
        $fe->getMessage()->willReturn('code_here');

        $this->getFormErrorCode($fe);
    }
}

class NewConstraint extends Constraint
{
}
