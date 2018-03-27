<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

use DeskPRO\Bundle\AppBundle\Entity\FilterInterface;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;

class PhpEnginePreCompileEvent extends PhpEngineEvent
{
    /**
     * @var TicketFilter
     */
    private $filter;

    public function __construct(TermEngineContext $context, FilterInterface $filter)
    {
        parent::__construct($context);
        $this->filter = $filter;
    }

    /**
     * Retrieve the filter.
     *
     * @return FilterInterface the filter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
