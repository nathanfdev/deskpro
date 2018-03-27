<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketNumAgentReplies;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketNumAgentRepliesTermCompiler.
 */
class DbalTicketNumAgentRepliesTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getNumericHelper()->buildQueryPart(
            'ticket.count_agent_replies',
            $term->getOp(),
            $term->getOption('num')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
