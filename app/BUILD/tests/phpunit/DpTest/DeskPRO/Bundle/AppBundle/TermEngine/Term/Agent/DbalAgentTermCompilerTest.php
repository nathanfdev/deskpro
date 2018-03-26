<?php

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
            [
                'agent_ids' => [1, 2, 15],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket.agent_id IN (:ids)');

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 2, 15],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testIsCompileWithMe()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [1, 2, 15, AgentTerm::ID_ME],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IN (:ids)'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testIsNotMe()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [1, 2, 15, AgentTerm::ID_ME],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id NOT IN (:ids)'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWithUnassigned()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [1, 2, 15, null],
            ],
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IN (:ids) OR ticket.agent_id IS NULL'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 2, 15],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testWithNOTUnassigned()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [0],
            ],
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
            [
                'agent_ids' => [0],
            ],
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
            [
                'agent_ids' => [1, 2, 15, AgentTerm::ID_ME, 0],
            ],
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id IN (:ids) OR ticket.agent_id IS NULL'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testNOTAllIdTypes()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [1, 2, 15, AgentTerm::ID_ME, 0],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_id NOT IN (:ids) AND ticket.agent_id IS NOT NULL'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    2,
                    15,
                    new TermEngineExpression('agent.getId()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testThatEmptyArraysAndNullsCountAsUnassigned()
    {
        $term = new AgentTerm(
            [
                'agent_ids' => [],
            ],
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
            [
                'agent_ids' => [null],
            ],
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
            [
                'agent_ids' => [null, null, null],
            ],
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
