<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateCreated;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketDateCreatedTermCompiler.
 */
class DbalTicketDateCreatedTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getDateHelper()->buildQueryPart(
            'ticket.date_created',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2'),
            $term->getOption('ignore_time')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
