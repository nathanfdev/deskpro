<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class QuickSearchEvent.
 */
class QuickSearchEvent extends Event
{
    /**
     * @var QuickSearchContext
     */
    private $context;

    /**
     * @var QuickSearchRequest
     */
    private $request;

    /**
     * Constructor.
     *
     * @param QuickSearchContext $context
     * @param QuickSearchRequest $request
     */
    public function __construct(QuickSearchContext $context, QuickSearchRequest $request)
    {
        $this->context = $context;
        $this->request = $request;
    }

    /**
     * @return QuickSearchContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return QuickSearchRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
