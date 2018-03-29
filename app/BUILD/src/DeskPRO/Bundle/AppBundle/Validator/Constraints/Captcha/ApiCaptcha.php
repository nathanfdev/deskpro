<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Captcha;

use Symfony\Component\Validator\Constraint;

/**
 * Class ApiCaptcha.
 */
class ApiCaptcha extends Constraint
{
    const CAPTCHA = 'captcha';

    public $message = 'The CAPTCHA value was incorrect';
}
