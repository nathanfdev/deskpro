<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketCreationSystemTermCompiler.
 */
class DbalTicketCreationSystemTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getStringHelper()->buildQueryPart(
            'ticket.creation_system',
            $term->getOp(),
            $term->getOption('creation_system'),
            false,
            true
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
