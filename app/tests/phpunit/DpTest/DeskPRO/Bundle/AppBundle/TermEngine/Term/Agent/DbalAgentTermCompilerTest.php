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
namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\DbalAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalAgentTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalAgentTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.agent');
    }

    public function testSimpleIsCompile()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15),
            )
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket.agent_id IN (:ids)');

        $this->assertParameters(
            $query_part,
            array(
                'ids' => array(1, 2, 15),
            )
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testIsCompileWithMe()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, AgentTerm::ID_ME),
            )
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IN (:ids)'
        );

        $this->assertParameters(
            $query_part,
            array(
                'ids' => array(
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ),
            )
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testIsNotMe()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, AgentTerm::ID_ME),
            ),
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id NOT IN (:ids)'
        );

        $this->assertParameters(
            $query_part,
            array(
                'ids' => array(
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ),
            )
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWithUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, null),
            ),
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IN (:ids) OR ticket.agent_id IS NULL'
        );

        $this->assertParameters(
            $query_part,
            array(
                'ids' => array(1, 2, 15),
            )
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWithNOTUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(0),
            ),
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IS NOT NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWithOnlyUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(0),
            ),
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IS NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testAllIdTypes()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, AgentTerm::ID_ME, 0),
            ),
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IN (:ids) OR ticket.agent_id IS NULL'
        );

        $this->assertParameters(
            $query_part,
            array(
                'ids' => array(
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ),
            )
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testNOTAllIdTypes()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(1, 2, 15, AgentTerm::ID_ME, 0),
            ),
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id NOT IN (:ids) AND ticket.agent_id IS NOT NULL'
        );

        $this->assertParameters(
            $query_part,
            array(
                'ids' => array(
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ),
            )
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testThatEmptyArraysAndNullsCountAsUnassigned()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(),
            ),
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IS NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testThatEmptyArraysAndNullsCountAsUnassigned2()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(null),
            ),
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IS NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testThatEmptyArraysAndNullsCountAsUnassigned3()
    {
        $term = new AgentTerm(
            array(
                'agent_ids' => array(null, null, null),
            ),
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IS NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
