<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Form\Error;

use DeskPRO\Bundle\AppBundle\Form\Error\ApiErrors;
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
        $this->getConstraintErrorCode($violation)->shouldReturn(ApiErrors::NOT_NULL);

        $violation->getConstraint()->willReturn(new NotBlank());
        $this->getConstraintErrorCode($violation)->shouldReturn(ApiErrors::NOT_BLANK);

        $violation->getConstraint()->willReturn(new Length(['min' => 5]));
        $this->getConstraintErrorCode($violation)->shouldReturn(ApiErrors::LENGTH_TOO_SHORT);

        $violation->getConstraint()->willReturn(new Type(['type' => 'null']));
        $this->getConstraintErrorCode($violation)->shouldReturn(ApiErrors::INVALID_DATA_TYPE);

        $violation->getConstraint()->willReturn(new Valid());
        $this->getConstraintErrorCode($violation)->shouldReturn(ApiErrors::INVALID_INPUT);
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

        $this->getConstraintErrorCode($violation)->shouldReturn(ApiErrors::CONSTRAINT_FALLBACK);
    }

    public function it_treats_form_transformation_exception_as_a_type_error(
        FormError $fe,
        ConstraintViolation $violation,
        TransformationFailedException $exception
    ) {
        $fe->getCause()->willReturn($violation);
        $violation->getCause()->willReturn($exception);
        $violation->getMessage()->willReturn(null);

        $this->getFormErrorCode($fe)->shouldReturn(ApiErrors::INVALID_DATA_TYPE);
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
