<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class FormCaptchaFactory
{
    public static function create(DeskproContainer $container, $options = [])
    {
        $captcha = new \Application\DeskPRO\Form\Captcha\Recaptcha($container);

        return $captcha;
    }
}
