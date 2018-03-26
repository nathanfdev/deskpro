<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketLabelTermCompiler.
 */
class DbalTicketLabelTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getJoinedHelper()->buildQueryPart(
            'labels_tickets.label',
            'labels_tickets',
            'ticket.id = labels_tickets.ticket_id',
            $term->getOp(),
            $term->getOption('label'),
            false
        );

        return $qp;
    }
}
