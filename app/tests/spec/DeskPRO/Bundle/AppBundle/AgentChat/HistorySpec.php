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
namespace spec\DeskPRO\Bundle\AppBundle\AgentChat;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\AgentChat\History;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use DeskPRO\Bundle\AppBundle\AgentChat\Search\Doctrine as DoctrineSearcher;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat as AgentChatRepo;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatParticipant as AgentChatParticipantRepo;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin History
 */
class HistorySpec extends ObjectBehavior
{
    public function let(
        DoctrineSearcher $searcher,
        EntityManager $em,
        DepartmentDataService $dataService,
        AgentChatParticipantRepo $participantRepo,
        AgentChatRepo $chatRepo,
        AgentChat $chat1,
        AgentChat $chat2
    ) {
        $this->beConstructedWith($searcher, $em, $dataService);
        $chat1->getId()->willReturn(1);
        $chat2->getId()->willReturn(2);
        $searcher->searchInChat(Argument::type('DeskPRO\Bundle\AppBundle\Entity\AgentChat'), Argument::any())->willReturn(array());
        $em->getRepository('App:AgentChatParticipant')->willReturn($participantRepo);
        $em->getRepository('App:AgentChat')->willReturn($chatRepo);
        $participantRepo->findChatsIds(Argument::type('Application\DeskPRO\Entity\Person'), Argument::any())->willReturn(array(1, 2));
        $chatRepo->findAllChats(Argument::any(), Argument::any())->willReturn(array($chat1, $chat2));
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\AgentChat\History');
    }
    public function it_can_get_all_messages_of_the_chat(AgentChat $chat, AgentChatMessage $message)
    {
        $chat->addMessage($message);
        $chat->getMessages()->willReturn(array());
        $this->getChatHistory($chat)->shouldBeArray();
        $chat->getMessages()->shouldBeCalled();
    }
    public function it_can_search_messages_through_chat(AgentChat $chat, AgentChatMessage $message)
    {
        $message->setMessage('test');
        $chat->addMessage($message);
        $this->searchInChat($chat, 'test', 'dateCreated')->shouldBeArray();
    }
    public function it_can_find_all_person_chats(Person $bob)
    {
        $this->findChats($bob)->shouldBeArray();
    }
    public function it_can_find_all_messages_among_all_user_chats(Person $alice)
    {
        $this->searchAllMessages($this->findChats($alice), 'where is the Red Queen?')->shouldBeArray();
    }
    public function it_can_search_string_through_all_persons_chats(
        Person $JohnnyMnemonic,
        AgentChatMessage $message,
        AgentChat $FriendsChat,
        AgentChat $FoesChat
    ) {
        $message->setMessage('where is Johnny?');
        $JohnnyMnemonic->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $FriendsChat->setType(Chatable::PARTICIPANT_TYPE_AGENT);
        $FriendsChat->addMessage($message);
        $FoesChat->addMessage($message);
        $FriendsChat->addParticipant($JohnnyMnemonic);
        $FoesChat->addParticipant($JohnnyMnemonic);

        $this->searchAllChats([$FriendsChat], 'where is Johnny?')->shouldBeArray();
    }
}
