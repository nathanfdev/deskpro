<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketUrgencyTermCompiler.
 */
class DbalTicketUrgencyTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getNumericHelper()->buildQueryPart(
            'ticket.urgency',
            $term->getOp(),
            $term->getOption('num')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
