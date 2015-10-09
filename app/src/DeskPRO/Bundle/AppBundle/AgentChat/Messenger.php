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
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\AgentChat\Exceptions\WrongChatableTypeException;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat as AgentChatRepository;

class Messenger
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param AgentChat $chat
     * @param Person    $person
     * @param $message
     *
     * @return AgentChatMessage
     */
    public function addMessage(AgentChat $chat, Person $person, $message)
    {
        $agentMessage = new AgentChatMessage();
        $agentMessage->setPerson($person)
            ->setMessage($message)
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
            case Chatable::PARTICIPANT_TYPE_PERSON:
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
        return $this->createChat([$agent, $user], Chatable::PARTICIPANT_TYPE_PERSON);
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

    /**
     * @param $id
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
            case Chatable::PARTICIPANT_TYPE_PERSON:
                $entity_name = 'DeskPRO:Person';
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM:
                $entity_name = 'DeskPRO:AgentTeam';
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT:
                $entity_name = 'DeskPRO:Department';
                break;
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
            case Chatable::PARTICIPANT_TYPE_PERSON:
                $chats = $agentChatRepository->findChatWithAgent($target->getId(), $user->getId());
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM:
                $chats = $agentChatRepository->findTeamChat($target->getId());
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT:
                $chats = $agentChatRepository->findDepartmentChat($target->getId());
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
        foreach ($chat->getPersonList() as $participant) {
            if ($person->getId() === $participant->getId()) {
                return true;
            }
        }

        return false;
    }
}
