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
