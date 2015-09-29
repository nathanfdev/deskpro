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

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use PhpSpec\ObjectBehavior;

/**
 * @mixin AgentChatMessage
 */
class AgentChatMessageSpec extends ObjectBehavior
{
    public function it_is_returns_chat_it_belongs_to(AgentChat $chat)
    {
        $this->setChat($chat);
        $this->getChat()->shouldBeEqualTo($chat);
    }
    public function it_work_propper_with_its_setter_and_getters(Person $badSanta, AgentChat $chat)
    {
        $metadata = array(
            'key1' => 'value1',
            'key2' => 'value2',
        );
        $badSanta->getDisplayName()->willReturn('Billy Bob Thornton');
        $this->setChat($chat);
        $this->setMetadata($metadata);
        $this->setMessage('test');
        $this->setPerson($badSanta);
        $this->getMetadata()->shouldBeArray();
        $this->getMetadata()->shouldBeEqualTo($metadata);
        $this->getPerson()->shouldBeEqualTo($badSanta);
        $this->getPersonName()->shouldBeEqualTo('Billy Bob Thornton');
    }
}
