<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpTest\DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\TermCompiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

class DbalAgentTeamTermCompilerTest extends AbstractDbalTicketFilterTermCompilerTest
{
    public function testSimpleIsCompile()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199)
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id IN (:agent_team_ids_0)',
            array(
                'agent_team_ids_0' => array(1, 3, 199)
            )
        );
    }

    public function testSimpleIsNOTCompile()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199)
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id NOT IN (:agent_team_ids_0)',
            array(
                'agent_team_ids_0' => array(1, 3, 199)
            )
        );
    }

    public function testSimpleIsWithMeCompile()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199, 'me')
            )
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id IN (:agent_team_ids_0) OR ticket.agent_team_id IN (:me_0)',
            array(
                'agent_team_ids_0' => array(1, 3, 199),
                'me_0' => new TermEngineExpression('agent.getTeamIds()')
            )
        );
    }

    public function testNotWithMeCompile()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199, 'me')
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id NOT IN (:agent_team_ids_0) AND ticket.agent_team_id NOT IN (:me_0)',
            array(
                'agent_team_ids_0' => array(1, 3, 199),
                'me_0' => new TermEngineExpression('agent.getTeamIds()')
            )
        );
    }

    public function testNotUnassignedCompile()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199, 'unassigned')
            ),
            TermInterface::OP_IS
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id IN (:agent_team_ids_0) OR ticket.agent_team_id IS NULL',
            array(
                'agent_team_ids_0' => array(1, 3, 199)
            )
        );
    }

    public function testOnlyUnassigned()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array('unassigned')
            ),
            TermInterface::OP_IS
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id IS NULL',
            array()
        );
    }

    public function testOnlyNotUnassigned()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array('unassigned')
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id IS NOT NULL',
            array()
        );
    }

    public function testCompileSeveral()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199, 'unassigned', 'me')
            ),
            TermInterface::OP_IS
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id IN (:agent_team_ids_0) OR ticket.agent_team_id IN (:me_0) OR ticket.agent_team_id IS NULL',
            array(
                'agent_team_ids_0' => array(1, 3, 199),
                'me_0' => new TermEngineExpression('agent.getTeamIds()')
            )
        );
    }

    public function testCompileSeveralNOT()
    {
        $term = new AgentTeamTerm(
            array(
                'agent_team_ids' => array(1, 3, 199, 'unassigned', 'me')
            ),
            TermInterface::OP_NOT
        );

        $compiled_query = $this->compileTerm($term);

        $this->assertCompiledQuery(
            $compiled_query,
            'ticket.agent_team_id NOT IN (:agent_team_ids_0) AND ticket.agent_team_id NOT IN (:me_0) AND ticket.agent_team_id IS NOT NULL',
            array(
                'agent_team_ids_0' => array(1, 3, 199),
                'me_0' => new TermEngineExpression('agent.getTeamIds()')
            )
        );
    }
}
