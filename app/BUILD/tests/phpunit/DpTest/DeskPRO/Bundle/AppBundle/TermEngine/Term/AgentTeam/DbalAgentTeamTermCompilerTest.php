<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\DbalAgentTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractDbalTicketFilterTermCompilerTest;

class DbalAgentTeamTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    /**
     * @var DbalAgentTermCompiler
     */
    protected $term_compiler;

    public function setUp()
    {
        $this->term_compiler = $this->get('term_engine.dbal_ticket_filters.compiler.agent_team');
    }

    public function testSimpleIsCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket.agent_team_id IN (:ids)');

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 3, 199],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleIsNOTCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere($query_part, 'ticket.agent_team_id NOT IN (:ids)');

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 3, 199],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testSimpleIsWithMeCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, AgentTeamTerm::TEAM_ID_ME],
            ]
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id IN (:ids)'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    3,
                    199,
                    new TermEngineExpression('agent.getTeamIds()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testNotWithMeCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, AgentTeamTerm::TEAM_ID_ME],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id NOT IN (:ids)'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    3,
                    199,
                    new TermEngineExpression('agent.getTeamIds()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testNotUnassignedCompile()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, 0],
            ],
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id IN (:ids) OR ticket.agent_team_id IS NULL'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [1, 3, 199],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testOnlyUnassigned()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [0],
            ],
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id IS NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testOnlyNotUnassigned()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [0],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id IS NOT NULL'
        );

        $this->assertNoParameters($query_part);
        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileSeveral()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, 0, AgentTeamTerm::TEAM_ID_ME],
            ],
            TermInterface::OP_IS
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id IN (:ids) OR ticket.agent_team_id IS NULL'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    3,
                    199,
                    new TermEngineExpression('agent.getTeamIds()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }

    public function testCompileSeveralNOT()
    {
        $term = new AgentTeamTerm(
            [
                'agent_team_ids' => [1, 3, 199, 0, AgentTeamTerm::TEAM_ID_ME],
            ],
            TermInterface::OP_NOT
        );

        $query_part = $this->term_compiler->compile($term);

        $this->assertWhere(
            $query_part,
            'ticket.agent_team_id NOT IN (:ids) AND ticket.agent_team_id IS NOT NULL'
        );

        $this->assertParameters(
            $query_part,
            [
                'ids' => [
                    1,
                    3,
                    199,
                    new TermEngineExpression('agent.getTeamIds()'),
                ],
            ]
        );

        $this->assertNoJoins($query_part);
        $this->assertNoUniqueJoins($query_part);
    }
}
