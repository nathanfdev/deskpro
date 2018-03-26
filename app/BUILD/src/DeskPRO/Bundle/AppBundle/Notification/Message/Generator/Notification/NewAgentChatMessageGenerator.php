<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NewAgentChatMessageGenerator.
 */
class NewAgentChatMessageGenerator extends AbstractGenerator
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * NewAgentChatMessageGenerator constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AvatarResolver        $avatarResolver
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage, AvatarResolver $avatarResolver)
    {
        parent::__construct($em, $tokenStorage);
        $this->avatarResolver = $avatarResolver;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event)
    {
        /* @var NewMessageEvent $event */
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            $messages[] = new Notification($target, $this->getData($event), $event->getName());
        }

        return $messages;
    }

    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof NewMessageEvent) {
            return true;
        }

        return false;
    }

    protected function getTargets(NewMessageEvent $event)
    {
        $message = $this->getChatMessage($event);
        $targets = [];
        foreach ($message->getChat()->getPersonList() as $target) {
            /** @var Person $target */
            if (is_object($this->getUser()) && $target->getId() !== $this->getUser()->getId()) {
                $targets[] = $target->getId();
            }
        }

        return $targets;
    }

    protected function getData(NewMessageEvent $event)
    {
        $message = $this->getChatMessage($event);
        $data    = [
            'chatId'  => $message->getChat()->getId(),
            'title'   => sprintf('%s sent a message to you', $message->getPersonName()),
            'summary' => $message->getMessage(),
            'icon'    => $this->avatarResolver->getAvatar($message->getPerson(), 64),
        ];

        return $data;
    }

    protected function getChatMessage(NewMessageEvent $event)
    {
        $messageRepo = $this->em->getRepository(AgentChatMessage::class);
        /** @var AgentChatMessage $message */
        $message = $messageRepo->findOneBy(['id' => $event->getMessageId()]);
        if (!$message) {
            throw new \InvalidArgumentException(sprintf('No message with id [ %s ] was found!', $event->getMessageId()));
        }

        return $message;
    }
}
