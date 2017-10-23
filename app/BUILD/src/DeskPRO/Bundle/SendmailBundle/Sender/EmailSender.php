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

namespace DeskPRO\Bundle\SendmailBundle\Sender;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\EmailBundle\SwiftMailer\Mailer;
use Application\EmailBundle\SwiftMailer\Message\Message;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\PortalBundle\Model\EmailTo;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContainerInterface
     */
    private $container;

    /**
     * EmailSender constructor.
     *
     * @param EmailRenderer      $emailRenderer
     * @param Mailer             $mailer
     * @param EntityManager      $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(
        EmailRenderer $emailRenderer,
        Mailer $mailer,
        EntityManager $entityManager,
        ContainerInterface $container
    ) {
        $this->renderer      = $emailRenderer;
        $this->mailer        = $mailer;
        $this->entityManager = $entityManager;
        $this->container     = $container;
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

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param EmailBaseType $model
     * @param $args
     * @param Message $message
     *
     * @throws \Exception
     *
     * @return Message
     */
    public function prepareMessage(EmailBaseType $model, $args, $message = null)
    {
        if (!$message) {
            $message = $this->getMailer()->createMessage();
        }
        /** @var PersonRepository $personRepository */
        $personRepository = $this->getEntityManager()->getRepository(Person::class);
        if (is_string($args['to'])) {
            $recipient = $personRepository->findOneByEmail($args['to']);
        } elseif (is_a($args['to'], EmailTo::class)) {
            /** @var EmailTo $emailTo */
            $emailTo = $args['to'];
            if ($emailTo->getPerson()) {
                $recipient = $emailTo->getPerson();
            } else {
                $recipient = $personRepository->findOneByEmail($emailTo->getEmailAddress());
            }
        } elseif (is_a($args['to'], Person::class)) {
            $recipient = $args['to'];
        } elseif (!$message->getTo()) {
            throw new \Exception('Missing required "to" argument');
        }
        if (!empty($recipient)) {
            $serializationContext = new SideloadSerializationContext();
            $serializationContext->setInlineSideloads(true);
            $person = $this->container->get('api_serializer.handler.person')
                ->createModel($recipient, $serializationContext);
            $model->setRecipient($person);
            $message->setToPerson($recipient);
        } elseif (is_a($args['to'], EmailTo::class)) {
            $emailTo = $args['to'];
            $message->setTo($emailTo->getEmailAddress(), $emailTo->getName());
        } elseif ($args['to']) {
            $message->setTo($args['to']);
        }
        $template = isset($args['template']) ? $args['template'] : $model->getTemplate();

        $emailCode = $this->getRenderer()->render($template, $model);
        $message->setBody($emailCode->getBody(), 'text/html');
        $message->setSubject($emailCode->getSubject());
        foreach ($emailCode->getAttachments() as $blob) {
            $message->attachBlob($blob);
        }
        if (!empty($args['attachments'])) {
            foreach ($args['attachments'] as $attach) {
                $message->attach($attach);
            }
        }

        if (isset($args['headers'])) {
            foreach ($args['headers'] as $header) {
                $message->getHeaders()->addTextHeader($header['name'], $header['value']);
            }
        }

        if (isset($args['from_account'])) {
            $message->setFrom($args['from_account']->getUseEmailAddress(), $args['from_name']);
        }

        if (!empty($args['Message-ID'])) {
            $message->getHeaders()->get('Message-ID')->setId($args['Message-ID']);
        }

        return $message;
    }

    /**
     * @param EmailBaseType $model
     * @param array         $args
     *
     * @throws \Exception
     */
    public function send(EmailBaseType $model, $args)
    {
        $message = $this->prepareMessage($model, $args);
        $this->mailer->send($message);
    }
}
