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

namespace spec\DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AgentChat\Exceptions\WrongChatableTypeException;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use PhpSpec\ObjectBehavior;

/**
 * @mixin AgentChat
 */
class AgentChatSpec extends ObjectBehavior
{
    public function it_can_return_person_list_builded_from_different_sources(
        Person $AngelinaJolie,
        Person $BradPitt,
        AgentTeam $XMenTeam,
        Person $Wolverine,
        Person $Phoenix,
        Person $Cyclops
    ) {
        $AngelinaJolie->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $BradPitt->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $XMenTeam->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_TEAM);
        $XMenTeam->addPerson($Wolverine);
        $XMenTeam->addPerson($Phoenix);
        $XMenTeam->addPerson($Cyclops);
        $this->addParticipant($AngelinaJolie, $BradPitt, $XMenTeam);
        $this->getPersonList()->shouldBeArray();
        $personList = $this->getPersonList();
        foreach ($personList as $person) {
            $person->shouldHaveType('Application\DeskPRO\Entity\Person');
        }
    }
    public function it_can_add_participant(
        Person $VanHelsing,
        Person $VladDracula,
        AgentTeam $HelsingTeam,
        Department $DemonHunters
    ) {
        $VanHelsing->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_AGENT);
        $HelsingTeam->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_TEAM);
        $DemonHunters->getChatableType()->willReturn(Chatable::PARTICIPANT_TYPE_DEPARTMENT);
        $this->addParticipant($VanHelsing);
        $this->shouldThrow(new WrongChatableTypeException())->during('addParticipant', array($VladDracula));
    }
    public function it_can_add_message_to_itself_and_return_it(
        AgentChatMessage $message
    ) {
        $this->addMessage($message);
        $this->getMessages()->shouldHaveType('IteratorAggregate');
        $messages = $this->getMessages();
        foreach ($messages as $message) {
            $message->shouldHaveType('DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage');
        }
    }
}
