<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateFirstAgentReply;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketDateFirstAgentReplyTermCompiler.
 */
class DbalTicketDateFirstAgentReplyTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getDateHelper()->buildQueryPart(
            'ticket.date_first_agent_reply',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2'),
            $term->getOption('ignore_time')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
