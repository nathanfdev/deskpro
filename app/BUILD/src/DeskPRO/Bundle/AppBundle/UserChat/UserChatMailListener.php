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

namespace DeskPRO\Bundle\AppBundle\UserChat;

use Application\EmailBundle\SwiftMailer\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class UserChatMailListener.
 */
class UserChatMailListener implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param Mailer          $mailer
     * @param RouterInterface $router
     */
    public function __construct(Mailer $mailer, RouterInterface $router)
    {
        $this->mailer = $mailer;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            UserChatEvent::VALIDATE_EMAIL => 'onSendValidationCode',
        ];
    }

    /**
     * @param UserChatEvent $event
     */
    public function onSendValidationCode(UserChatEvent $event)
    {
        $conversation = $event->getChat();
        $validate_url = $this->router->generate(
            'portal_chats_validate_email',
            [
                'chat' => $conversation->getId(),
                'code' => $conversation->getEmailValidationCode(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $message = $this->mailer->createMessage();
        $message->setTo($conversation->getPersonEmail(), $conversation->getPersonName());
        $message->setTemplate('AppBundle:Email/Chat:validate-email.html.twig', [
            'validation_code' => $conversation->getEmailValidationCode(),
            'validation_url'  => $validate_url,

        ]);
        $message->prepare();

        $this->mailer->send($message);
    }
}
