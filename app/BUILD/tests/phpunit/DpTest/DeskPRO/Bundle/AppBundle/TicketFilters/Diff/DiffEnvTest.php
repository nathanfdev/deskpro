<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use DeskPRO\Bundle\AppBundle\TicketFilters\Diff\DiffEnv;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Agent;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketMatcher;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\Util\ListUtils;

require_once __DIR__.'/FilterData.php';

class DiffEnvTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return DiffEnv
     */
    private function makeEnv()
    {
        $resolver = new ValueResolver();
        $matcher  = new TicketMatcher($resolver, [
            new TicketBasicTermsHandler(),
        ]);

        $agentContexts = FilterData::getAgents();
        $filters       = FilterData::getFilters();

        $env = new DiffEnv($matcher, $agentContexts, $filters);

        return $env;
    }

    public function testAgentPermCheck()
    {
        $env    = $this->makeEnv();
        $agents = $env->getAgents();

        $ticketA             = new TicketModel();
        $ticketA->id         = 1;
        $ticketA->status     = 'awaiting_user';
        $ticketA->department = 1;

        $ticketB             = new TicketModel();
        $ticketB->id         = 1;
        $ticketB->status     = 'awaiting_agent';
        $ticketB->department = 3;

        $this->assertTrue($agents[0]->canViewTicket($ticketA));
        $this->assertTrue($agents[1]->canViewTicket($ticketA));
        $this->assertFalse($agents[2]->canViewTicket($ticketA));

        $this->assertTrue($agents[0]->canViewTicket($ticketB));
        $this->assertFalse($agents[1]->canViewTicket($ticketB));
        $this->assertTrue($agents[2]->canViewTicket($ticketB));
    }

    public function testFiltersWithField()
    {
        $env = $this->makeEnv();

        $match = ListUtils::map($env->getFiltersWithAnyField(['ticket.status', 'ticket.agent']), function ($v) {
            return $v->id;
        });

        $this->assertEquals(
            [1, 2, 3, 4, 5],
            $match
        );
    }

    public function testFilterIsUnique()
    {
        $env = $this->makeEnv();

        $this->assertTrue($env->isFilterContextUnique(1));
        $this->assertTrue($env->isFilterContextUnique(2));
        $this->assertTrue($env->isFilterContextUnique(3));
        $this->assertFalse($env->isFilterContextUnique(4));
        $this->assertFalse($env->isFilterContextUnique(5));
    }
}
