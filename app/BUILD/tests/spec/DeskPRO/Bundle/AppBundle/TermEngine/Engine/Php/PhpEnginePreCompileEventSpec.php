<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

use DeskPRO\Bundle\AppBundle\Entity\FilterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEnginePreCompileEvent
 */
class PhpEnginePreCompileEventSpec extends ObjectBehavior
{
    public function let(TermEngineContext $context, FilterInterface $filter)
    {
        $this->beConstructedWith($context, $filter);
    }

    public function it_is_a_phpengine_event()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEngineEvent');
    }

    public function it_also_has_the_filter_being_evaluated(FilterInterface $filter)
    {
        $this->getFilter()->shouldBe($filter);
    }
}
