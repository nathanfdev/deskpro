<?php

namespace DpUnitTests\DeskPRO\Tickets\Triggers\Terms;

require_once 'AbstractTicketEntityCheckTest.php';

class CheckAgentTeamTest extends AbstractTicketEntityCheckTest
{
    /**
     * {@inheritdoc}
     */
    protected function getCheckClass()
    {
        return 'Application\\DeskPRO\\Tickets\\Triggers\\Terms\\CheckAgentTeam';
    }

    /**
     * {@inheritdoc}
     */
    protected function getCheckClassOptionKey()
    {
        return 'team_ids';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Application\\DeskPRO\\Entity\\AgentTeam';
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketPropertyName()
    {
        return 'agent_team';
    }
}
