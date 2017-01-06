<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
