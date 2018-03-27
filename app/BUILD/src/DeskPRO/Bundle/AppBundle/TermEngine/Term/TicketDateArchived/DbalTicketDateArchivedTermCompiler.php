<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateArchived;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketDateArchivedTermCompiler.
 */
class DbalTicketDateArchivedTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $date   = $term->getOption('date');
        $date2  = $term->getOption('date2');
        $ignore = $term->getOption('ignore_time');

        $qp = $this->getDateHelper()->buildQueryPart(
            'ticket.date_archived',
            $term->getOp(),
            $date,
            $date2,
            $ignore
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
