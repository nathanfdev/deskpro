<?php

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\Query\DbalQuery;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DbalEngineEvent.
 */
class DbalEngineEvent extends Event
{
    protected $query;
    protected $context;

    /**
     * Constructor.
     *
     * @param DbalQuery         $query
     * @param TermEngineContext $context
     */
    public function __construct(DbalQuery $query, TermEngineContext $context)
    {
        $this->query   = $query;
        $this->context = $context;
    }

    /**
     * @return DbalQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return TermEngineContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
