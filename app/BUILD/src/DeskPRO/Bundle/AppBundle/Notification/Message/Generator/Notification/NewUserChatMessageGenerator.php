<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\People\Helpers\AgentPermissions;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\SystemEventGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NewUserChatMessageGenerator.
 */
class NewUserChatMessageGenerator extends SystemEventGenerator
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * @var int[]
     */
    private $availableAgents;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AgentDataService      $agentDataService
     * @param AvatarResolver        $avatarResolver
     */
    public function __construct(
        EntityManager $em,
        TokenStorageInterface $tokenStorage,
        AgentDataService $agentDataService,
        AvatarResolver $avatarResolver
    ) {
        parent::__construct($em, $tokenStorage, $agentDataService);
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event)
    {
        /* @var UserChatEvent $event */
        $messages = [];

        $data = $event->getData();

        if (isset($data['agent']) && $data['agent']) {
            $messages[] = new Notification($data['agent'], $this->getData($event), $event->getName());
        } else {
            foreach ($this->getTarget($event) as $target) {
                if (in_array($target, $this->getAvailableAgents()) && $this->checkPermissions($target, $event->getData())) {
                    $messages[] = new Notification($target, $this->getData($event), $event->getName());
                }
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
        if ($event instanceof UserChatEvent && $event->getEventType() === ClientMessageEvent::CHANNEL_CHAT_NEW) {
            return true;
        }

        return false;
    }

    /**
     * @param UserChatEvent $event
     *
     * @return array
     */
    private function getData(UserChatEvent $event)
    {
        $data = $event->getData();

        return [
            'title'   => 'New incoming chat',
            'summary' => 'New incoming chat ['.$data['subject'].'] by '
                .($data['person_name'] ? $data['person_name'] : 'anonymous')
                ."\r\nDepartment: ".$data['department_name'],
            'icon' => isset($data['author_id']) ? $this->getAvatar($data['author_id']) : null,
        ];
    }

    /**
     * @param $authorId
     *
     * @return string|null
     */
    private function getAvatar($authorId)
    {
        $person = $this->em->find(Person::class, $authorId);
        if ($person) {
            return $this->avatarResolver->getAvatar($person);
        }

        return;
    }

    /**
     * @return int[]
     */
    private function getAvailableAgents()
    {
        if (!$this->availableAgents) {
            /** @var PersonRepository $personRepository */
            $personRepository      = $this->em->getRepository(Person::class);
            $this->availableAgents = $personRepository->getActiveAgentIdsForUserChat();
        }

        return $this->availableAgents;
    }

    /**
     * @param $target
     * @param $data
     *
     * @return bool
     */
    private function checkPermissions($target, $data)
    {
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);
        /** @var Person $person */
        $person = $personRepository->find($target);
        /** @var AgentPermissions $agentPermissions */
        $agentPermissions = $person->getHelper('AgentPermissions');

        return in_array($data['department'], $agentPermissions->getAllowedDepartments('chat'));
    }
}
