<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateFirstAgentReply;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketDateFirstAgentReplyTermCompiler.
 */
class PhpTicketDateFirstAgentReplyTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getDateHelper()->buildQueryPart(
            'ticket.date_first_agent_reply',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2')
        );
    }
}
