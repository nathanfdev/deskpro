<?php

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
