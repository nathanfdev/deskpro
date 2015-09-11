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
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat as AgentChatRepository;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use Application\DeskPRO\ORM\EntityManager;

class Messenger
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
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
     * @param Person $person
     * @return AgentChat $chat
     */
    public function createChat(Person $person, array $participants)
    {
        $chat = new AgentChat();
        $chat->addParticipant($person);
        foreach($participants as $participant) {
            $chat->addParticipant($participant);
        }
        return $chat;
    }

    public function getChat($id)
    {
        /** @var AgentChatRepository $agentChatRepository */
        $agentChatRepository = $this->em->getRepository('App:AgentChat');
        return $agentChatRepository->find($id);
    }

    public function isPersonInvolvedInChat(Person $person, AgentChat $chat)
    {
        foreach($chat->getPersonList() as $participant) {
            if($person->getId() === $participant->getId()) {
                return true;
            }
        }
        return false;
    }
}