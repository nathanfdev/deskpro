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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\HistorySearcher;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat as AgentChatRepository;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatMessage as AgentChatMessageRepository;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatParticipant as AgentChatParticipantRepository;

/**
 * Class History.
 */
class History
{
    /**
     * @var HistorySearcher
     */
    protected $searcher;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param HistorySearcher       $searcher
     * @param EntityManager         $em
     * @param DepartmentDataService $department_data_service
     */
    public function __construct(
        HistorySearcher $searcher,
        EntityManager $em,
        DepartmentDataService $department_data_service
    ) {
        $this->searcher                = $searcher;
        $this->em                      = $em;
        $this->department_data_service = $department_data_service;
    }

    /**
     * @param AgentChat $chat
     *
     * @return AgentChatMessage[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getChatHistory(AgentChat $chat)
    {
        return $chat->getMessages();
    }

    /**
     * @param AgentChat $chat
     * @param string    $searchString
     * @param string    $orderBy
     *
     * @return array
     */
    public function searchInChat(AgentChat $chat, $searchString, $orderBy)
    {
        $ids    = $this->getSearcher()->searchInChat($chat, $searchString);
        $result = [];
        if ($ids) {
            $messageRepo = $this->em->getRepository('App:AgentChatMessage');
            $messages    = $messageRepo->findBy(array('id' => $ids), array($orderBy => 'DESC'));
            foreach ($messages as $message) {
                /* @var AgentChatMessage $message */
                $result[$message->getId()] = $message;
            }
        }

        return $result;
    }

    /**
     * @param AgentChat[] $chats
     * @param string      $searchString
     *
     * @return AgentChatMessage[]
     */
    public function searchAllMessages(array $chats, $searchString)
    {
        $messages = array();
        foreach ($chats as $chat) {
            $messages = array_merge($messages, $this->getSearcher()->searchInChat($chat, $searchString));
        }

        return $messages;
    }

    /**
     * @param AgentChat[] $chats
     * @param string      $searchString
     *
     * @return AgentChat[]
     */
    public function searchAllChats(array $chats, $searchString)
    {
        $filtered = array_filter($chats, function ($chat) use ($searchString) {
            return $this->getSearcher()->searchInChat($chat, $searchString);
        });

        return $filtered;
    }

    /**
     * @param Person $person
     * @param array  $order
     *
     * @return AgentChat[]
     */
    public function findChats(Person $person, $order = ['date_last_message' => 'DESC'])
    {
        $departments_ids = [];
        if ($departments = $this->department_data_service->getChatDepartmentsForPerson($person)) {
            foreach ($departments as $department) {
                $departments_ids[] = $department->getId();
            }
        }

        /** @var AgentChatParticipantRepository $repo */
        $repo = $this->em->getRepository('App:AgentChatParticipant');
        $ids  = $repo->findChatsIds($person, $departments_ids);
        /* @var AgentChatRepository $chatRepo */
        $chatRepo = $this->em->getRepository('App:AgentChat');
        $chats    = $chatRepo->findAllChats($ids, $order);

        return $chats;
    }

    /**
     * @param Person $user
     *
     * @return array
     */
    public function countMessages(Person $user)
    {
        $chats = $this->findChats($user);
        /** @var AgentChatMessageRepository $repo */
        $repo = $this->em->getRepository('App:AgentChatMessage');

        return $repo->countMessages($user, $chats);
    }

    /**
     * @return HistorySearcher
     */
    protected function getSearcher()
    {
        return $this->searcher;
    }
}
