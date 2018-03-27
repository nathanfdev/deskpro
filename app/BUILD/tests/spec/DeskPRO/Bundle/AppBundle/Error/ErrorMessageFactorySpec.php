<?php

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
        $this->beConstructedWith($translate, 'api.error_codes.');
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
        $form_error->getMessageParameters()->willReturn([]);
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
        $form_error->getMessageParameters()->willReturn([]);
        $form_error->getMessage()->willReturn('This form should not contain extra fields.');

        $translate->phrase('api.error_codes.extra_fields', [])->willReturn('extra fields: email');

        $this->createFormErrorMessage(ErrorsCodes::BAD_REQUEST, $form_error)->shouldReturn('extra fields: email');
    }
}
