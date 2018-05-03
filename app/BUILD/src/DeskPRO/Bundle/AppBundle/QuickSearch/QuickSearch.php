<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\QuickSearch;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class QuickSearch.
 */
class QuickSearch
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param QuickSearchRequest $request
     *
     * @return QuickSearchResponse
     */
    public function search(QuickSearchRequest $request)
    {
        $response = new QuickSearchResponse();
        foreach ($request->getTypes() as $type) {
            $response->createContext($type);
        }

        if ($request->getQuery()) {
            foreach ($response->getContexts() as $context) {
                $this->dispatcher->dispatch(QuickSearchEvents::SEARCH, new QuickSearchEvent($context, $request));
                $this->dispatcher->dispatch(QuickSearchEvents::POST_SEARCH, new QuickSearchEvent($context, $request));
            }
        }

        foreach ($response->getContexts() as $context) {
            $this->dispatcher->dispatch(QuickSearchEvents::FINISH, new QuickSearchEvent($context, $request));
        }

        return $response;
    }
}
