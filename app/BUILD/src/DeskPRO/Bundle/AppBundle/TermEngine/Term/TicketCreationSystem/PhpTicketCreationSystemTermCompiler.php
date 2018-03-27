<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCreationSystem;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketCreationSystemTermCompiler.
 */
class PhpTicketCreationSystemTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getStringHelper()->buildQueryPart(
            'ticket.creation_system',
            $term->getOp(),
            $term->getOption('creation_system'),
            false,
            true
        );
    }
}
