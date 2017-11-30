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

namespace DpTest\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\OrgModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\PersonModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\OrgTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\PersonTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketDateTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueChecker;
use DeskPRO\Component\FilterQueryLanguage\Parser;

class TicketMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TicketModel
     */
    private $ticket1;

    /**
     * @var TicketModel
     */
    private $ticket2;

    /**
     * @var AgentContext
     */
    private $agentContext;

    /**
     * @var TicketMatcher
     */
    private $matcher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $checker = new ValueChecker($this->agentContext, function () {
        });
        $this->matcher = new TicketMatcher([
            new TicketBasicTermsHandler($checker),
            new TicketDateTermsHandler(),
            new PersonTermsHandler(),
            new OrgTermsHandler(),
        ]);

        // Ticket 1
        $this->ticket1             = new TicketModel();
        $this->ticket1->id         = 1;
        $this->ticket1->agent      = 1;
        $this->ticket1->agent_team = 1;
        $this->ticket1->department = 1;
        $this->ticket1->followers  = [100, 102];
        $this->ticket1->labels     = ['label1', 'label2'];

        $this->ticket1->person         = new PersonModel();
        $this->ticket1->person->labels = ['plabel1', 'plabel2'];

        $this->ticket1->organization         = new OrgModel();
        $this->ticket1->organization->labels = ['olabel1', 'olabel2'];

        // Ticket 2
        $this->ticket2             = new TicketModel();
        $this->ticket2->id         = 2;
        $this->ticket2->agent      = 2;
        $this->ticket2->agent_team = 2;
        $this->ticket2->department = 2;
        $this->ticket2->followers  = [103, 104];
        $this->ticket2->labels     = ['label3', 'label4'];

        $this->ticket2->person         = new PersonModel();
        $this->ticket2->person->labels = ['plabel3', 'plabel4'];

        $this->ticket2->organization         = new OrgModel();
        $this->ticket2->organization->labels = ['olabel3', 'olabel4'];

        // Agent context
        $this->agentContext                      = new AgentContext();
        $this->agentContext->id                  = 2;
        $this->agentContext->allowed_departments = [1, 100];
        $this->agentContext->view_assigned       = true;
        $this->agentContext->view_unassigned     = true;
        $this->agentContext->teams               = [2, 3];
    }

    public function test_id_match()
    {
        $this->assertTrue($this->runTicket1Query('ticket.id = 1'));
        $this->assertfalse($this->runTicket1Query('ticket.id != 2'));
        $this->assertTrue($this->runTicket1Query('ticket.id < 10'));
        $this->assertFalse($this->runTicket1Query('ticket.id > 10'));
        $this->assertTrue($this->runTicket1Query('ticket.id <= 1'));
        $this->assertTrue($this->runTicket1Query('ticket.id BETWEEN 1 AND 100'));

        $this->assertFalse($this->runTicket2Query('ticket.id = 1'));
    }

    private function runTicket1Query($fql)
    {
        return $this->matcher->doesQueryMatch(
            $this->parseFql($fql),
            $this->ticket1,
            $this->agentContext
        );
    }

    private function runTicket2Query($fql)
    {
        return $this->matcher->doesQueryMatch(
            $this->parseFql($fql),
            $this->ticket2,
            $this->agentContext
        );
    }

    /**
     * @param $fql
     *
     * @return array
     */
    private function parseFql($fql)
    {
        $parser = new Parser();

        return $parser->parseQuery($fql);
    }
}
