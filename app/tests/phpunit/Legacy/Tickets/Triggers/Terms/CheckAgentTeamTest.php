<?php
namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckAgentTeamTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritDoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckAgentTeam';
    }

    /**
     * {@inheritDoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'team_ids';
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\AgentTeam';
    }

    /**
     * {@inheritDoc}
     */
    public function getTicketPropertyName()
    {
        return 'agent_team';
    }
}
