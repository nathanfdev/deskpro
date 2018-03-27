<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketNumUserReplies;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketNumUserRepliesTermCompiler.
 */
class PhpTicketNumUserRepliesTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $num = $term->getOption('num');

        return new PhpCheck(
            'check_contains(ticket.count_user_replies, :op, :num)',
            [
                'op'  => $op,
                'num' => $num,
            ]
        );
    }
}
