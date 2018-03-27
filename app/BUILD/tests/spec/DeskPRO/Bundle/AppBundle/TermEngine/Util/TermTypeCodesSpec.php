<?php

/**
 * DeskPRO.
 */

namespace spec\DeskPRO\Bundle\AppBundle\TermEngine\Util;

use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\TermEngine\Util\TermTypeCodes
 */
class TermTypeCodesSpec extends ObjectBehavior
{
    public function it_maps_term_codes_for_you()
    {
        // just rely on this service to always give you the correct code for your term object
        $this->getTermTypeCode(new AgentTerm())->shouldBe('agent');
        $this->getTermTypeCode(new CompositeTerm())->shouldBe('composite');
        $this->getTermTypeCode(new TicketStatusTerm())->shouldBe('ticket_status');

        // and back again
        $this->getTermClassForTypeCode('agent')->shouldBe('DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm');
        $this->getTermClassForTypeCode('composite')->shouldBe('DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm');
        $this->getTermClassForTypeCode('ticket_status')->shouldBe(
            'DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm'
        );
    }
}
