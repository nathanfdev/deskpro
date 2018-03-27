<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketStatusTermCompiler.
 */
class PhpTicketStatusTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op           = $term->getOp();
        $status_codes = $term->getOption('status');

        return new PhpCheck(
            'check_contains(ticket.getStatusCode(), :op, :status_codes)',
            [
                'op'           => $op,
                'status_codes' => $status_codes,
            ]
        );
    }
}
