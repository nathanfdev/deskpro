<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketRef;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php\TermCompiler\AbstractPhpTermCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class PhpTicketRefTermCompiler.
 */
class PhpTicketRefTermCompiler extends AbstractPhpTermCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function doCompile(TermInterface $term)
    {
        return $this->getStringHelper()->buildQueryPart(
            'ticket.ref',
            $term->getOp(),
            $term->getOption('ref')
        );
    }
}
