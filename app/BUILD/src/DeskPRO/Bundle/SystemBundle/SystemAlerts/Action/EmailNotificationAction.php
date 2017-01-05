<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SystemBundle\SystemAlerts\Action;

use Application\DeskPRO\Entity\Person;
use Application\EmailBundle\SwiftMailer\Mailer;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use Doctrine\ORM\EntityRepository;

/**
 * Class EmailNotificationAction.
 */
class EmailNotificationAction
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var EntityRepository
     */
    private $people;

    /**
     * @param Mailer           $mailer
     * @param EntityRepository $people
     */
    public function __construct(Mailer $mailer, EntityRepository $people)
    {
        $this->mailer = $mailer;
        $this->people = $people;
    }

    /**
     * Send email notifications to all admins.
     *
     * This method allows the following macros inside the message body:
     * - {{incident_title}}
     *
     * @param Incident $incident
     * @param string   $from
     * @param string   $subject
     * @param string   $body
     */
    public function notifyAdmins(Incident $incident, $from, $subject, $body)
    {
        /** @var Person[] $admins */
        $admins = $this->people->findBy(['can_admin' => true]);
        $to     = [];
        foreach ($admins as $admin) {
            $to[] = $admin->getEmailAddress();
        }

        $body = str_replace('{{incident_title}}', $incident->getTitle(), $body);

        $this->send($from, $to, $subject, $body);
    }

    /**
     * Send email using the mailer service and fallback on PHP mail() if failed.
     *
     * @param string $from
     * @param array  $to
     * @param string $subject
     * @param string $body
     */
    private function send($from, array $to, $subject, $body)
    {
        $message = \Swift_Message::newInstance();
        $message->setFrom($from);
        $message->setTo($to);
        $message->setSubject($subject);
        $message->setBody($body);

        if (!$this->mailer->send($message)) {
            @mail(implode(',', $to), $subject, $body, "From: {$from}\r\n");
        }
    }
}
