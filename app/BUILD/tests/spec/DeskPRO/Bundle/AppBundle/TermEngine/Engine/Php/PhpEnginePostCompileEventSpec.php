<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEnginePostCompileEvent
 */
class PhpEnginePostCompileEventSpec extends ObjectBehavior
{
    public function let(TermEngineContext $context, TicketFilter $filter, PhpCheck $php_check)
    {
        $this->beConstructedWith($context, $filter, $php_check);
    }

    public function it_is_a_phpengine_event()
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpEngineEvent');
    }

    public function it_also_has_the_filter_being_evaluated(TicketFilter $filter)
    {
        $this->getFilter()->shouldBe($filter);
    }

    public function it_also_has_the_compiled_php_class(PhpCheck $php_check)
    {
        $this->getPhpCheck()->shouldBe($php_check);
    }
}
