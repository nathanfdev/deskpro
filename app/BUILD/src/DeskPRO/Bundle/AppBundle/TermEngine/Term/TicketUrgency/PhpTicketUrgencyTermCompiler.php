<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketUrgencyTermCompiler.
 */
class PhpTicketUrgencyTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op  = $term->getOp();
        $num = $term->getOption('num');

        return new PhpCheck(
            'check_contains(ticket.urgency, :op, :num)',
            [
                'op'  => $op,
                'num' => $num,
            ]
        );
    }
}
