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
        $params = array(
            'num' => array(1, 2, 3),
        );

        $term       = new TicketIdTerm($params);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNOTCase()
    {
        $params = array(
            'num' => array(1, 2, 3),
        );

        $term       = new TicketIdTerm($params, TermInterface::OP_NOT);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id NOT IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTCase()
    {
        $params = array(
            'num' => array(1, 2, 3),
        );

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
        $params = array(
            'num' => array(1, 2, 3),
        );

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
        $params = array(
            'num' => array(1, 2, 3),
        );

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
        $params = array(
            'num' => array(1, 2, 3),
        );

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
        $params = array(
            'num'  => array(1, 2, 3),
            'num2' => 3,
        );

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
        $params = array(
            'num'  => array(1, 2, 3),
            'num2' => 3,
        );

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
        $params = array(
            'num' => array(1),
        );

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
        $params = array(
            'num' => array(1),
        );

        $term          = new TicketIdTerm($params, TermInterface::OP_NOT_RANGE);
        $params['num'] = $params['num2'] = reset($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.id NOT BETWEEN :num AND :num2');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
