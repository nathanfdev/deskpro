<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert\Messenger;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ChatGenerator.
 */
class ChatGenerator extends AbstractGenerator
{
    /**
     * @var AvatarResolver
     */
    protected $avatarResolver;

    /**
     * ChatGenerator constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AvatarResolver        $avatarResolver
     */
    public function __construct(EntityManager $em, TokenStorageInterface $tokenStorage, AvatarResolver $avatarResolver)
    {
        $this->avatarResolver = $avatarResolver;
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return array
     */
    private function getTargets(SystemEventInterface $event)
    {
        /** @var ChatEvent $event */
        $chat           = $this->getChat($event);
        $agentTargets   = $chat->getAgentParticipants() ?: [];
        $userTargets    = $chat->getUserParticipants() ?: [];
        $visitorTargets = $chat->getVisitorId() ?: '';

        return array_merge($agentTargets, $userTargets, [$visitorTargets]);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return ChatConversation
     */
    private function getChat(SystemEventInterface $event)
    {
        /** @var ChatEvent $event */
        $repository = $this->em->getRepository(ChatConversation::class);

        return $repository->find($event->getChatId());
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return array|\DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event)
    {
        /** @var ChatEvent $event */
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            if ($target && $target !== $this->getUser()->getId()) {
                if ($target instanceof Person) {
                    $target = $target->getId();
                }

                $messages[] = new ActionAlert($target, $this->getData($event), $event->getType());
            }
        }

        return $messages;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof ChatEvent;
    }

    /**
     * @param ChatEvent $event
     *
     * @return array
     */
    private function getData(ChatEvent $event)
    {
        $chat = $this->getChat($event);

        return [
            'origin' => 'system',
            'name'   => $chat->getPersonName(),
            'email'  => $chat->getPersonEmail(),
            'avatar' => $chat->getAgent() ? $this->avatarResolver->getAvatar($chat->getAgent()) : '',
            'data'   => $event->getData(),
        ];
    }
}
