<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketNumAgentReplies;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketNumAgentRepliesTermCompiler.
 */
class PhpTicketNumAgentRepliesTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $num = $term->getOption('num');

        return new PhpCheck(
            'check_contains(ticket.count_agent_replies, :op, :num)',
            [
                'op'  => $op,
                'num' => $num,
            ]
        );
    }
}
