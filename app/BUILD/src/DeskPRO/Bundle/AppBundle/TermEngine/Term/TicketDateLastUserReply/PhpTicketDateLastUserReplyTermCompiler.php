<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateLastUserReply;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketDateLastUserReplyTermCompiler.
 */
class PhpTicketDateLastUserReplyTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getDateHelper()->buildQueryPart(
            'ticket.date_last_user_reply',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2')
        );
    }
}
