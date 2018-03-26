<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketFlaggedTermCompiler.
 */
class PhpTicketFlaggedTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op    = $term->getOp();
        $color = $term->getOption('flag');

        return new PhpCheck(
            'check_contains(helper_pool.getHelper(\'agent\').getFlags(ticket, agent), :op, :flag)',
            [
                'op'   => $op,
                'flag' => $color,
            ]
        );
    }
}
