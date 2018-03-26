<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalAgentTeamTermCompiler.
 */
class DbalAgentTeamTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $ids = [];
        foreach ($term->getOption('agent_team_ids') as $teamId) {
            if ($teamId === AgentTeamTerm::TEAM_ID_ME) {
                $ids[] = new TermEngineExpression('agent.getTeamIds()');
            } else {
                $ids[] = $teamId;
            }
        }

        $qp = $this->getEntityHelper()->buildQueryPart(
            'ticket.agent_team_id',
            $term->getOp(),
            $ids
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
