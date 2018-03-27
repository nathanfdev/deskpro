<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler\DbalCompiler;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQueryBuilder;

/**
 * Class DbalTicketFilterCompiler.
 */
class DbalTicketFilterCompiler extends DbalCompiler
{
    /**
     * {@inheritdoc}
     */
    protected function enginePreCompile(DbalQueryBuilder $writer)
    {
        $writer->setFrom('tickets', 'ticket');
    }

    /**
     * {@inheritdoc}
     */
    protected function enginePostCompile(DbalQueryBuilder $query_writer)
    {
    }
}
