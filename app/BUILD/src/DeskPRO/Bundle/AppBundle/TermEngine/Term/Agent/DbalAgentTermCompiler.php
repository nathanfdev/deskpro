<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Expression\TermEngineExpression;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalAgentTermCompiler.
 */
class DbalAgentTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $ids = [];
        foreach ($term->getOption('agent_ids') as $agentId) {
            if ($agentId === AgentTerm::ID_ME) {
                $ids[] = new TermEngineExpression('agent.getId()');
            } else {
                $ids[] = $agentId;
            }
        }

        $qp = $this->getEntityHelper()->buildQueryPart(
            'ticket.agent_id',
            $term->getOp(),
            $ids
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
