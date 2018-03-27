<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketRefTermCompiler.
 */
class DbalTicketRefTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getStringHelper()->buildQueryPart(
            'ticket.ref',
            $term->getOp(),
            $term->getOption('ref')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
