<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateResolved;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketDateResolvedTermCompiler.
 */
class PhpTicketDateResolvedTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getDateHelper()->buildQueryPart(
            'ticket.date_resolved',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2')
        );
    }
}
