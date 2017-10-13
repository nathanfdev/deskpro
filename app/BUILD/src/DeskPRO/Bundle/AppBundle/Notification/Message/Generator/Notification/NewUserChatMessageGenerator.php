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
        foreach ($this->getTarget($event) as $target) {
            if (in_array($target, $this->getAvailableAgents()) && $this->checkPermissions($target, $event->getData())) {
                $messages[] = new Notification($target, $this->getData($event), $event->getName());
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
