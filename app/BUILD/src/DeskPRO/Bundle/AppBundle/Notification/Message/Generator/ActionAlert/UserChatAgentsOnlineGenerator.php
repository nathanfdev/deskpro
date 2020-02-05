<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\AgentStatusChangedEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\UserChatAgentsOnlineEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\AgentInfo;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserChatAgentsOnlineGenerator.
 */
class UserChatAgentsOnlineGenerator extends AbstractGenerator
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * UserChatAgentsOnlineGenerator constructor.
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
     * {@inheritdoc}
     *
     * @var UserChatAgentsOnlineEvent
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var UserChatAgentsOnlineEvent $event */
        $messages = [];

        if ($event instanceof AgentStatusChangedEvent) {
            $actionAlert = new ActionAlert(NotificationService::TARGET_USER_BROADCAST,
                [
                    'online'   => $event->getOnline(),
                    'agent'    => $this->getAgent($event->getPersonId()),
                ],
                $event->getName());
            $actionAlert->setBroadcast();
            $messages[] = $actionAlert;
        } else {
            $avatarResolver = $this->avatarResolver;
            $em             = $this->em;
            $data           = array_map(function ($agentId) use ($avatarResolver, $em) {
                if ($agent = $em->find(Person::class, $agentId)) {
                    $agentInfo = new AgentInfo($agent, $avatarResolver);

                    return $agentInfo->toArray();
                } else {
                    return ['id' => $agentId];
                }
            }, $event->getAgentIds());
            $actionAlert = new ActionAlert(NotificationService::TARGET_USER_BROADCAST, $data, $event->getName());
            $actionAlert->setBroadcast();
            $messages[] = $actionAlert;
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof UserChatAgentsOnlineEvent || $event instanceof AgentStatusChangedEvent) {
            return true;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return array
     */
    private function getAgent($id)
    {
        $person = $this->em->find(Person::class, $id);
        if ($person) {
            $agentInfo = new AgentInfo($person, $this->avatarResolver);

            return $agentInfo->toArray();
        } else {
            return ['id' => $id];
        }
    }
}
