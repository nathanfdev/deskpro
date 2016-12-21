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

use Application\DeskPRO\Entity\Person;
use Application\EmailBundle\SwiftMailer\Mailer;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Doctrine\ORM\EntityManager;

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
     * @var EntityManager
     */
    private $entityManager;

    /**
     * EmailSender constructor.
     *
     * @param EmailRenderer $emailRenderer
     * @param Mailer        $mailer
     * @param EntityManager $container
     */
    public function __construct(EmailRenderer $emailRenderer, Mailer $mailer, EntityManager $container)
    {
        $this->renderer      = $emailRenderer;
        $this->mailer        = $mailer;
        $this->entityManager = $container;
    }

    /**
     * @return EmailRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * @return Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EmailBaseType $model
     * @param array         $args
     */
    public function send(EmailBaseType $model, $args)
    {
        $recipient = $this->getEntityManager()->getRepository(Person::class)->findOneByEmail($args['to']);
        if ($recipient) {
            $model->setRecipient($recipient);
        }
        $emailCode = $this->getRenderer()->render($model->getTemplate(), $model);
        $message   = $this->getMailer()->createMessage();
        $message->setTo($args['to']);
        $message->setBody($emailCode->getBody());
        $message->setSubject($emailCode->getSubject());
        foreach ($emailCode->getAttachments() as $blob) {
            $message->attachBlob($blob);
        }
        $this->mailer->send($message);
    }
}
