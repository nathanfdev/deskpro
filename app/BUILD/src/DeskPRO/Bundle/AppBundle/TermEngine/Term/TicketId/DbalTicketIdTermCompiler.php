<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketId;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketIdTermCompiler.
 */
class DbalTicketIdTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getNumericHelper()->buildQueryPart(
            'ticket.id',
            $term->getOp(),
            $term->getOption('num'),
            $term->getOption('num2')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
