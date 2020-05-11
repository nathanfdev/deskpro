<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class HcValidRecaptcha2 extends Constraint
{
    public $message = 'helpcenter.forms.error_captcha';

    public function validatedBy()
    {
        return ValidRecaptcha2Validator::class;
    }
}
