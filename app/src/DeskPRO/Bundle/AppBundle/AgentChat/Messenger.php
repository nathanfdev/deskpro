<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\AgentChat;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AgentChat\Exceptions\WrongChatableTypeException;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use DeskPRO\Bundle\AppBundle\Entity\EveryoneChat;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat as AgentChatRepository;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatMessage as AgentChatMessageRepository;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Messenger
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var DepartmentDataService
     */
    protected $department_data_service;

    /**
     * @param EntityManager            $em
     * @param DepartmentDataService    $department_data_service
     * @param EventDispatcherInterface $event_dispatcher
     */
    public function __construct(
        EntityManager $em,
        DepartmentDataService $department_data_service,
        EventDispatcherInterface $event_dispatcher
    ) {
        $this->em                      = $em;
        $this->department_data_service = $department_data_service;
        $this->event_dispatcher        = $event_dispatcher;
    }

    /**
     * @param AgentChat $chat
     * @param Person    $person
     * @param string    $message
     * @param string    $uuid
     * @return AgentChatMessage
     */
    public function addMessage(AgentChat $chat, Person $person, $message, $uuid)
    {
        $agentMessage = new AgentChatMessage();
        $agentMessage->setPerson($person)
            ->setMessage($message)
            ->setUuid($uuid)
            ->setMetadata(array());
        $chat->addMessage($agentMessage);
        $this->em->persist($chat);
        $this->em->persist($agentMessage);
        $this->em->flush();

        return $agentMessage;
    }

    /**
     * @param array  $participants
     * @param string $type
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\AgentChat $chat
     */
    public function createChat(array $participants, $type)
    {
        $chat = new AgentChat();
        $chat->setType($type);
        foreach ($participants as $participant) {
            $chat->addParticipant($participant);
        }

        return $chat;
    }

    /**
     * @param Person   $user
     * @param Chatable $target
     *
     * @throws WrongChatableTypeException
     *
     * @return AgentChat
     */
    public function startChat(Person $user, Chatable $target)
    {
        switch ($target->getChatableType()) {
            case Chatable::PARTICIPANT_TYPE_AGENT:
                /* @var Person $target */
                return $this->createChatWithAgent($user, $target);
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM:
                /* @var AgentTeam $target */
                return $this->createChatWithTeam($target);
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT:
                /* @var Department $target */
                return $this->createChatWithDepartment($target);
                break;
            case Chatable::PARTICIPANT_TYPE_EVERYONE:
                return $this->createEveryoneChat();
                break;
            default:
                throw new WrongChatableTypeException();
        }
    }

    /**
     * @param Person $user
     * @param Person $agent
     *
     * @return AgentChat
     */
    public function createChatWithAgent(Person $user, Person $agent)
    {
        return $this->createChat([$agent, $user], Chatable::PARTICIPANT_TYPE_AGENT);
    }

    /**
     * @param AgentTeam $team
     *
     * @return AgentChat
     */
    public function createChatWithTeam(AgentTeam $team)
    {
        return $this->createChat([$team], Chatable::PARTICIPANT_TYPE_TEAM);
    }

    /**
     * @param Department $department
     *
     * @return AgentChat
     */
    public function createChatWithDepartment(Department $department)
    {
        return $this->createChat([$department], Chatable::PARTICIPANT_TYPE_DEPARTMENT);
    }

    public function createEveryoneChat()
    {
        return $this->createChat([], Chatable::PARTICIPANT_TYPE_EVERYONE);
    }

    /**
     * @param            $id
     * @param bool|false $forceReload
     *
     * @return null|object
     */
    public function getChat($id, $forceReload = false)
    {
        /** @var AgentChatRepository $agentChatRepository */
        $agentChatRepository = $this->em->getRepository('App:AgentChat');

        return !$forceReload ? $agentChatRepository->find($id) : $agentChatRepository->findOneBy(['id' => $id]);
    }

    public function getChats(array $ids)
    {
        /** @var AgentChatRepository $agentChatRepository */
        $agentChatRepository = $this->em->getRepository('App:AgentChat');

        return $agentChatRepository->findBy(['id' => $ids]);
    }

    /**
     * @param $type
     * @param $id
     *
     * @throws WrongChatableTypeException
     *
     * @return null|Chatable
     */
    public function findParticipant($type, $id)
    {
        switch ($type) {
            case Chatable::PARTICIPANT_TYPE_AGENT:
                $entity_name = 'DeskPRO:Person';
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM:
                $entity_name = 'DeskPRO:AgentTeam';
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT:
                $entity_name = 'DeskPRO:Department';
                break;
            case Chatable::PARTICIPANT_TYPE_EVERYONE:
                return new EveryoneChat();
            default:
                throw new WrongChatableTypeException();
        }

        $repo = $this->em->getRepository($entity_name);

        return $repo->find((int) $id);
    }

    /**
     * @param Person   $user
     * @param Chatable $target
     *
     * @throws WrongChatableTypeException
     *
     * @return bool|AgentChat
     */
    public function findChat(Person $user, Chatable $target)
    {
        /** @var AgentChatRepository $agentChatRepository */
        $agentChatRepository = $this->em->getRepository('App:AgentChat');

        switch ($target->getChatableType()) {
            case Chatable::PARTICIPANT_TYPE_AGENT:
                $chats = $agentChatRepository->findChatWithAgent($target->getId(), $user->getId());
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM:
                $chats = $agentChatRepository->findTeamChat($target->getId());
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT:
                $chats = $agentChatRepository->findDepartmentChat($target->getId());
                break;
            case Chatable::PARTICIPANT_TYPE_EVERYONE:
                $chats = $agentChatRepository->findEveryoneChat();
                break;
            default:
                throw new WrongChatableTypeException();
        }

        if ($chats) {
            return array_shift($chats);
        }

        return false;
    }

    /**
     * @param Person    $person
     * @param AgentChat $chat
     *
     * @return bool
     */
    public function isPersonInvolvedInChat(Person $person, AgentChat $chat)
    {
        switch ($chat->getType()) {
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT:
                /** @var PersistentCollection $participants */
                $participants = $chat->getParticipants();
                /** @var AgentChatParticipant $participant */
                if ($participant = $participants->count() > 0 ? $participants->get(0) : false) {
                    $departments = $this->department_data_service->getChatDepartmentsForPerson($person);
                    foreach ($departments as $department) {
                        if ($department->getId() === $participant->getDepartmentId()) {
                            return true;
                        }
                    }
                }
                break;
            case Chatable::PARTICIPANT_TYPE_AGENT:
            case Chatable::PARTICIPANT_TYPE_TEAM:
                $participants = $chat->getPersonList();
                foreach ($participants as $participant) {
                    if ($person->getId() === $participant->getId()) {
                        return true;
                    }
                }
                break;
            case Chatable::PARTICIPANT_TYPE_EVERYONE:
                return true;
            default:
                return false;
        }

        return false;
    }

    /**
     * @param array  $ids
     * @param int    $status
     * @param Person $user
     */
    public function markMessages(array $ids, $status, Person $user)
    {
        /** @var AgentChatMessageRepository $repo */
        $repo = $this->em->getRepository('App:AgentChatMessage');
        if ($messages = $repo->findBy(['id' => $ids])) {
            foreach ($messages as $message) {
                /** @var AgentChatMessage $message */
                if ($this->isPersonInvolvedInChat($user, $message->getChat())) {
                    $message->setStatus($status);
                    $this->em->persist($message);
                    $this->event_dispatcher->dispatch(
                        MarkMessageEvent::EVENT_NAME,
                        new MarkMessageEvent($message->getId(), $status)
                    );
                }
                // TODO handle not-mine access violation
            }
        }
        $this->em->flush();
    }
}
