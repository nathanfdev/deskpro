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
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant;
use PhpSpec\ObjectBehavior;

/**
 * @mixin AgentChatParticipant
 */
class AgentChatParticipantSpec extends ObjectBehavior
{
    public function it_cat_set_and_get_chat(AgentChat $chat)
    {
        $this->setChat($chat);
        $this->getChat()->shouldBeEqualTo($chat);
    }

    public function it_can_deal_with_person_or_team_or_department_but_only_with_one(
        AgentTeam $team,
        Department $department,
        Person $person
    ) {
        $this->setTeam($team);
        $this->getTeam()->shouldBeEqualTo($team);
        $this->getPerson()->shouldBeEqualTo(null);
        $this->getDepartment()->shouldBeEqualTo(null);
        $this->setPerson($person);
        $this->getPerson()->shouldBeEqualTo($person);
        $this->getTeam()->shouldBeEqualTo(null);
        $this->getDepartment()->shouldBeEqualTo(null);
        $this->setDepartment($department);
        $this->getDepartment()->shouldBeEqualTo($department);
        $this->getPerson()->shouldBeEqualTo(null);
        $this->getTeam()->shouldBeEqualTo(null);
    }

    public function it_can_return_person_list_even_it_references_just_one_person(
        Person $tomCat,
        Person $jerryMouse,
        AgentTeam $cartoon,
        Person $Producer
    ) {
        $cartoon->addPerson($tomCat);
        $cartoon->addPerson($jerryMouse);
        $cartoon->getPersonList()->willReturn(
            array(
                $tomCat,
                $jerryMouse,
            )
        );
        $this->setTeam($cartoon);
        $this->getPersonList()->shouldBeArray();
        $cartoon->getPersonList()->shouldBeCalled();
        $this->setPerson($Producer);
        $this->getPersonList()->shouldBeArray();
    }
}
