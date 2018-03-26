<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TermCompiler\AbstractDbalTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTicketSubjectTermCompiler.
 */
class DbalTicketSubjectTermCompiler extends AbstractDbalTermCompiler
{
    /**
     * {@inheritdoc}
     */
    public function doCompile(TermInterface $term)
    {
        $qp = $this->getStringHelper()->buildQueryPart(
            'ticket.subject',
            $term->getOp(),
            $term->getOption('subject'),
            $term->getOption('wildcard_prefix'),
            $term->getOption('wildcard_postfix')
        );

        $this->logQueryPart($qp);

        return $qp;
    }
}
