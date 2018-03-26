<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ValidRecaptcha2 extends Constraint
{
    public $message = 'portal.forms.error_captcha';
}
