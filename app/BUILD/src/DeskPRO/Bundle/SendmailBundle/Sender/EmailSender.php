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

namespace DeskPRO\Bundle\SendmailBundle\Sender;

use Application\EmailBundle\SwiftMailer\Mailer;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DeskPRO\Bundle\SendmailBundle\ViewModel\EmailBaseType;

class EmailSender
{
    /**
     * @var EmailRenderer
     */
    private $renderer;

    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * EmailSender constructor.
     *
     * @param EmailRenderer $emailRenderer
     * @param Mailer        $mailer
     */
    public function __construct(EmailRenderer $emailRenderer, Mailer $mailer)
    {
        $this->renderer = $emailRenderer;
        $this->mailer   = $mailer;
    }

    /**
     * @param string        $templateName
     * @param array         $args
     * @param EmailBaseType $model
     */
    public function send($templateName, $args, EmailBaseType $model)
    {
        $emailBody = $this->renderer->render($templateName, $model);
        $message   = $this->mailer->createMessage();
        $message->setTo($args['to']);
        $message->setBody($emailBody);
        $this->mailer->send($message);
    }
}
