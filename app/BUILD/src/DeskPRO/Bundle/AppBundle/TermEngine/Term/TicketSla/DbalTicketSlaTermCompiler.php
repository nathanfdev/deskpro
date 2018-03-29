<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSla;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketSlaTermCompiler.
 */
class DbalTicketSlaTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $slas     = $term->hasOption('sla') ? $term->getOption('sla') : false;
        $statuses = $term->hasOption('status') ? $term->getOption('status') : false;

        $fields = [];

        if ($slas) {
            $fields['{ticket_slas}.sla_id'] = $slas;
        }
        if ($statuses) {
            $fields['{ticket_slas}.sla_status'] = $statuses;
        }

        $qp = $this->getJoinedHelper()->buildQueryPart(
            $fields,
            'ticket_slas',
            'ticket.id = {ticket_slas}.ticket_id',
            $term->getOp()
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
