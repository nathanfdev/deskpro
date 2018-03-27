<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketUrgencyTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency\TicketUrgencyTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketUrgencyTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketUrgencyTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_urgency');
    }

    public function testSimpleISCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term       = new TicketUrgencyTerm($params);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.urgency IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNOTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term       = new TicketUrgencyTerm($params, TermInterface::OP_NOT);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.urgency NOT IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketUrgencyTerm($params, TermInterface::OP_GT);
        $params['num'] = max($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.urgency > :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTECase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketUrgencyTerm($params, TermInterface::OP_GTE);
        $params['num'] = max($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.urgency >= :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleLTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketUrgencyTerm($params, TermInterface::OP_LT);
        $params['num'] = min($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.urgency < :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleLTECase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketUrgencyTerm($params, TermInterface::OP_LTE);
        $params['num'] = min($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.urgency <= :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
