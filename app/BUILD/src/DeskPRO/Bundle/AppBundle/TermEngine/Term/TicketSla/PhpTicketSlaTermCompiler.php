<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSla;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\PhpBuilder\PhpCheck;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketSlaTermCompiler.
 */
class PhpTicketSlaTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        $op = $term->getOp();

        $expressions = [];
        $values      = ['op' => $op];

        if ($term->hasOption('sla')) {
            $expressions[] = 'check_contains(ticket.ticket_slas, :op, :sla)';
            $values['sla'] = $term->getOption('sla');
        }
        if ($term->hasOption('status')) {
            $expressions[]    = 'check_contains(ticket.ticket_slas, :op, :status)';
            $values['status'] = $term->getOption('sla');
        }

        return new PhpCheck(
            implode(' and ', $expressions),
            $values
        );
    }
}
