<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketNumAgentReplies;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler\DbalTicketNumAgentRepliesTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketNumAgentReplies\TicketNumAgentRepliesTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalTicketNumAgentRepliesTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalTicketNumAgentRepliesTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.ticket_num_agent_replies');
    }

    public function testSimpleISCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term       = new TicketNumAgentRepliesTerm($params);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.count_agent_replies IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleNOTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term       = new TicketNumAgentRepliesTerm($params, TermInterface::OP_NOT);
        $query_part = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.count_agent_replies NOT IN (:num)');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketNumAgentRepliesTerm($params, TermInterface::OP_GT);
        $params['num'] = max($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.count_agent_replies > :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleGTECase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketNumAgentRepliesTerm($params, TermInterface::OP_GTE);
        $params['num'] = max($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.count_agent_replies >= :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleLTCase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketNumAgentRepliesTerm($params, TermInterface::OP_LT);
        $params['num'] = min($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.count_agent_replies < :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleLTECase()
    {
        $params = [
            'num' => [1, 2, 3],
        ];

        $term          = new TicketNumAgentRepliesTerm($params, TermInterface::OP_LTE);
        $params['num'] = min($params['num']);
        $query_part    = $this->term_compiler->compile($term);
        $this->assertParameters($query_part, $params);
        $this->assertWhere($query_part, 'ticket.count_agent_replies <= :num');

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
