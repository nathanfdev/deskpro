<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/
/**
 * DeskPRO
 *
 * @package DeskPRO
 */
namespace DeskPRO\Bundle\AppBundle\AgentChat;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\HistorySearcher;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat as AgentChatRepository;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatParticipant as AgentChatParticipantRepository;
use Application\DeskPRO\Entity\Person;

/**
 * Class History
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
     * @param HistorySearcher $searcher
     * @param EntityManager $em
     */
    public function __construct(HistorySearcher $searcher, EntityManager $em)
    {
        $this->searcher = $searcher;
        $this->em = $em;
    }

    /**
     * @param AgentChat $chat
     * @return AgentChatMessage[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getChatHistory(AgentChat $chat) {
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
        $ids = $this->getSearcher()->searchInChat($chat, $searchString);
        $messages = array();
        if($ids) {
            $messageRepo = $this->em->getRepository('App:AgentChatMessage');
            $messages = $messageRepo->findBy(array('id' => $ids), array($orderBy => 'DESC'));
        }
        return $messages;
    }

    /**
     * @param Person $person
     * @param string $searchString
     *
     * @return AgentChatMessage[]
     */
    public function searchAllMessages(Person $person, $searchString)
    {
        $chats = $this->findChats($person);
        $messages = array();
        foreach($chats as $chat) {
            $messages = array_merge($messages, $this->getSearcher()->searchInChat($chat, $searchString));
        }
        return $messages;
    }

    /**
     * @param Person $person
     * @param $searchString
     * @return AgentChat[]
     */
    public function searchAllChats(Person $person, $searchString)
    {
        $chats = $this->findChats($person);
        $filtered = [];
        if($searchString) {
            foreach($chats as $chat) {
                if($this->getSearcher()->searchInChat($chat, $searchString)) {
                    $filtered[] = $chat;
                }
            }
        } else {
            $filtered = $chats;
        }
        return $filtered;
    }

    /**
     * @param Person $person
     * @return AgentChat[]
     */
    public function findChats(Person $person) {
        /** @var AgentChatParticipantRepository $repo */
        $repo = $this->em->getRepository('App:AgentChatParticipant');
        $ids = $repo->findChatsIds($person);
        /** @var AgentChatRepository $repo */
        $chatRepo = $this->em->getRepository('App:AgentChat');
        $chats = $chatRepo->findBy(array('id' => $ids));
        return $chats;
    }

    /**
     * @return HistorySearcher
     */
    protected function getSearcher()
    {
        return $this->searcher;
    }
}