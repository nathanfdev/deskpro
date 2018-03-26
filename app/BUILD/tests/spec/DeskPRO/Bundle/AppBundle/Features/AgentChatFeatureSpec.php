<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\NewSettings\SettingsResolver;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\Features\AgentChatFeature
 */
class AgentChatFeatureSpec extends ObjectBehavior
{
    public function let(SettingsResolver $settingsResolver)
    {
        $this->beConstructedWith($settingsResolver);
    }

    public function it_returns_it_id()
    {
        $this->getId()->shouldBeEqualTo('agent_chat');
    }
}
