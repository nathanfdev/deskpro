<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Diff;

use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
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
            new TicketBasicTermsHandler($this->getMockBuilder(Elasticsearch::class)->disableOriginalConstructor()->getMock()),
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
            [1, 2, 3, 4, 5, 100, 101],
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
