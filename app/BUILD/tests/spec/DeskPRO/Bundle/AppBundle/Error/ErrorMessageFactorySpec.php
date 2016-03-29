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

use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Form\Error\ErrorMessageFactory
 */
class ErrorMessageFactorySpec extends ObjectBehavior
{
    public function let(Translate $translate)
    {
        $this->beConstructedWith($translate);
    }

    public function it_will_get_the_error_message_for_error_code(Translate $translate)
    {
        $translate->phrase('api.error_codes.bad_request', Argument::any())->willReturn('Request is invalid.');

        $this->createMessage(ErrorsCodes::BAD_REQUEST)->shouldReturn('Request is invalid.');
    }

    public function it_will_get_the_error_message_for_formerror_code(
        Translate $translate,
        FormError $form_error,
        ConstraintViolation $violation,
        Form $form_constraint
    ) {
        $form_error->getMessageParameters()->willReturn(array());
        $form_error->getMessage()->willReturn('irrelevant');
        $translate->phrase('api.error_codes.bad_request', Argument::any())->willReturn('Request is invalid.');

        $this->createFormErrorMessage(ErrorsCodes::BAD_REQUEST, $form_error)->shouldReturn('Request is invalid.');
    }

    public function it_treats_extra_fields_specially(
        Translate $translate,
        FormError $form_error,
        ConstraintViolation $violation,
        Form $form_constraint
    ) {
        $form_error->getMessageParameters()->willReturn(array());
        $form_error->getMessage()->willReturn('This form should not contain extra fields.');

        $translate->phrase('api.error_codes.extra_fields', array())->willReturn('extra fields: email');

        $this->createFormErrorMessage(ErrorsCodes::BAD_REQUEST, $form_error)->shouldReturn('extra fields: email');
    }
}
