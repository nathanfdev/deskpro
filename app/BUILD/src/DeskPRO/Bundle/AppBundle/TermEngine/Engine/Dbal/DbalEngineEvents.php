<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

/**
 * Class DbalEngineEvents.
 */
final class DbalEngineEvents
{
    /**
     * This is fired after a compiled query is about to be returned to be
     * executed by the DbalEngine. In here, we do last minute mutations
     * to the query, such as add agent permission checks, extra logging,
     * etc.
     */
    const MANIPULATE_QUERY = 'term_engine.dbal.manipulate_query';
}
