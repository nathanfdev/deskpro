<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine;

use Application\DeskPRO\Entity\Person;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext
 */
class TermEngineContextSpec extends ObjectBehavior
{
    public function let(
        Person $person
    ) {
        $this->beConstructedWith($person);
    }

    public function it_can_be_constructed_with_an_agent(
        Person $agent
    ) {
        $this->beConstructedWith($agent);

        $this->getAgent()->shouldBe($agent);
    }

    public function it_lets_you_change_agent(
        Person $agent1,
        Person $agent2
    ) {
        $this->beConstructedWith($agent1);

        $this->setAgent($agent2);

        $this->getAgent()->shouldBe($agent2);
    }
}
