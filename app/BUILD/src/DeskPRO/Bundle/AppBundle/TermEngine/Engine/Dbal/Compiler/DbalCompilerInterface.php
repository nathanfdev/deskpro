<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Compiler;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * We have compilers that do their job, but we also have caching versions that wrap them.
 *
 * The interface makes the "caching compiler" and the "real compiler" be interchangable.
 */
interface DbalCompilerInterface
{
    /**
     * Take a TermInterface and transform it into a DbalQuery.
     *
     * @param TermInterface $term
     *
     * @return DbalQuery
     */
    public function compile(TermInterface $term);
}
