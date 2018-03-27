<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateArchived;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketDateArchivedTermCompiler.
 */
class PhpTicketDateArchivedTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getDateHelper()->buildQueryPart(
            'ticket.date_archived',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2')
        );
    }
}
