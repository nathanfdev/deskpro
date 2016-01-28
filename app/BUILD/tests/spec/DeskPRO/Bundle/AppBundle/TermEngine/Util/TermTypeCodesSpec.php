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
