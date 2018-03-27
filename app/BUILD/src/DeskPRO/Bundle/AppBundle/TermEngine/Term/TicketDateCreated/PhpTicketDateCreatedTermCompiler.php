<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateCreated;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketDateCreatedTermCompiler.
 */
class PhpTicketDateCreatedTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getDateHelper()->buildQueryPart(
            'ticket.date_created',
            $term->getOp(),
            $term->getOption('date'),
            $term->getOption('date2')
        );
    }
}
