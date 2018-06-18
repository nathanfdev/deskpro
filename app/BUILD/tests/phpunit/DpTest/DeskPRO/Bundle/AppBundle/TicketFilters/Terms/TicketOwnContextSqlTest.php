<?php

namespace DpTest\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\NewSearch\Manager\Elasticsearch;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketBasicTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TicketOwnContextTermsHandler;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSqlMatcher;
use DpTest\Bundle\AppBundle\TicketFilters\AbstractTicketSqlMatcherTest;
use DpTestSrc\TestBundle\Mock\Dbal\ConnectionMock;

require_once __DIR__.'/../AbstractTicketSqlMatcherTest.php';

class TicketOwnContextSqlTest extends AbstractTicketSqlMatcherTest
{
    protected function setUpMatcher()
    {
        $db            = ConnectionMock::create();
        $this->matcher = new TicketSqlMatcher($this->valueResolver, [
            new TicketBasicTermsHandler($this->getMockBuilder(Elasticsearch::class)->disableOriginalConstructor()->getMock()),
            new TicketOwnContextTermsHandler($db),
        ], $db, TicketSqlMatcher::ACTIVE);

        $this->matcher->disableContextPermissions();
    }

    public function test_flag_set()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.starred EXISTS',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN tickets_flagged c1_flag ON c1_flag.ticket_id = tickets.id AND c1_flag.person_id = :c2
            WHERE (tickets.id = :c0) AND (c1_flag.color IS NOT NULL)
            ',
            ['c0' => 1, 'c2' => $this->matcherContext->getAgentId()]
        );
    }

    public function test_flag_not_set()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.starred IS EMPTY',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN tickets_flagged c1_flag ON c1_flag.ticket_id = tickets.id AND c1_flag.person_id = :c2
            WHERE (tickets.id = :c0) AND (c1_flag.color IS NULL)
            ',
            ['c0' => 1, 'c2' => $this->matcherContext->getAgentId()]
        );
    }

    public function test_flag_blue()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.starred = "blue"',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN tickets_flagged c1_flag ON c1_flag.ticket_id = tickets.id AND c1_flag.person_id = :c2
            WHERE (tickets.id = :c0) AND (c1_flag.color = :c3)
            ',
            ['c0' => 1, 'c2' => $this->matcherContext->getAgentId(), 'c3' => 'blue']
        );
    }

    public function test_flag_not_blue()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.starred != "blue"',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN tickets_flagged c1_flag ON c1_flag.ticket_id = tickets.id AND c1_flag.person_id = :c2
            WHERE (tickets.id = :c0) AND (c1_flag.color != :c3)
            ',
            ['c0' => 1, 'c2' => $this->matcherContext->getAgentId(), 'c3' => 'blue']
        );
    }

    public function test_flag_in()
    {
        $this->assertEqualQuery(
            'ticket.id = 1 AND ticket.starred IN ("blue", "red", "green")',
            'SELECT COUNT(*) AS count
            FROM tickets_search_active tickets
            LEFT JOIN tickets_flagged c1_flag ON c1_flag.ticket_id = tickets.id AND c1_flag.person_id = :c2
            WHERE (tickets.id = :c0) AND (c1_flag.color IN (:c3))
            ',
            ['c0' => 1, 'c2' => $this->matcherContext->getAgentId(), 'c3' => ['blue', 'red', 'green']]
        );
    }
}
