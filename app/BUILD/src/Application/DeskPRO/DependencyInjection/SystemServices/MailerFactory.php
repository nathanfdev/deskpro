<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Mail\Mailer;

class MailerFactory
{
    public static function create(DeskproContainer $container, $options = [])
    {
        $mailer = new Mailer(
            $container->getSystemService('email_account_manager'),
            $container->get('swiftmailer.transport'),
            $container->get('templating'),
            $container->get('deskpro.mail_logger')
        );

        return $mailer;
    }
}
