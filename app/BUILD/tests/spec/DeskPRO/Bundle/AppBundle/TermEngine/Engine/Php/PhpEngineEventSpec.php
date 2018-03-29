<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEngineEvent
 */
class PhpEngineEventSpec extends ObjectBehavior
{
    public function it_has_access_to_the_context(
        TermEngineContext $context
    ) {
        $this->beConstructedWith($context);

        $this->getContext()->shouldBe($context);
    }
}
