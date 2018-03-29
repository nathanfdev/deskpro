<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketId;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketIdTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketId\TicketIdTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketIdTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketIdTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_id');
    }

    public function testSimpleISCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term       = new TicketIdTerm($params);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNOTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term       = new TicketIdTerm($params, TermInterface::OP_NOT);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id NOT IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_GT);
        $params['num'] = max($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id > :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTECase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_GTE);
        $params['num'] = max($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id >= :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleLTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_LT);
        $params['num'] = min($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id < :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleLTECase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_LTE);
        $params['num'] = min($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id <= :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testMultipleRANGECase()
    {
        $params = [
            'num'  => [1, 2, 3],
            'num2' => 3,
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_RANGE);
        $params['num'] = reset($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id BETWEEN :num AND :num2');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testMultipleNOTRANGECase()
    {
        $params = [
            'num'  => [1, 2, 3],
            'num2' => 3,
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_NOT_RANGE);
        $params['num'] = reset($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id NOT BETWEEN :num AND :num2');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testOneNumRANGECase()
    {
        $params = [
            'num' => [1],
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_RANGE);
        $params['num'] = $params['num2'] = reset($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id BETWEEN :num AND :num2');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testOneNumNOTRANGECase()
    {
        $params = [
            'num' => [1],
        ];

        $term          = new TicketIdTerm($params, TermInterface::OP_NOT_RANGE);
        $params['num'] = $params['num2'] = reset($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id NOT BETWEEN :num AND :num2');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
