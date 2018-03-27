<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketSubject;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketSubjectTermCompiler.
 */
class PhpTicketSubjectTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getStringHelper()->buildQueryPart(
            'ticket.subject',
            $term->getOp(),
            $term->getOption('subject'),
            $term->getOption('wildcard_prefix'),
            $term->getOption('wildcard_postfix')
        );
    }
}
