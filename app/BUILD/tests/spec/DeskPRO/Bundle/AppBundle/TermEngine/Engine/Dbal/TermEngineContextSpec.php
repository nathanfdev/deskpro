<?php

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
