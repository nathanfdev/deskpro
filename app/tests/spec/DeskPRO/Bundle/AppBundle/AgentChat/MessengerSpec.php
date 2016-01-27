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

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use DeskPRO\Bundle\AppBundle\AgentChat\Messenger;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat as AgentChatEntity;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @mixin Messenger
 */
class MessengerSpec extends ObjectBehavior
{
    public function let(
        EntityManager $em,
        DepartmentDataService $dataService,
        AbstractEntityRepository $repo,
        AgentChatEntity $chat,
        EventDispatcherInterface $event_dispatcher)
    {
        $this->beConstructedWith($em, $dataService, $event_dispatcher);
        $em->getRepository('App:AgentChat')->willReturn($repo);
        $repo->find(1)->willReturn($chat);
        $chat->getId()->willReturn(1);
    }
    public function it_is_initializable()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\AgentChat\Messenger');
    }
    public function it_can_create_chat(Person $creator,
                                       Person $alice,
                                       Person $bob,
                                       Department $department,
                                       AgentTeam $team
    ) {
        $creator->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $alice->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $bob->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $department->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_DEPARTMENT);
        $team->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_TEAM);
        $participants = [$alice, $bob, $department, $team];
        $this->createChat($participants, Chatable::PARTICIPANT_TYPE_GROUP)->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\Entity\AgentChat');
        $this->createChat([$alice, $bob], Chatable::PARTICIPANT_TYPE_AGENT)->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\Entity\AgentChat');
        $this->createChat([$department], Chatable::PARTICIPANT_TYPE_DEPARTMENT)->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\Entity\AgentChat');
        $this->createChat([$team], Chatable::PARTICIPANT_TYPE_TEAM)->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\Entity\AgentChat');
    }
    public function it_can_get_chat()
    {
        $this->getChat(1)->shouldBeAnInstanceOf('DeskPRO\Bundle\AppBundle\Entity\AgentChat');
    }
}
