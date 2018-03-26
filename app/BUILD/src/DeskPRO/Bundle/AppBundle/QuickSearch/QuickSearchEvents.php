<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\QuickSearch;

/**
 * Class QuickSearchEvents.
 */
final class QuickSearchEvents
{
    /**
     * Collects entities and entity ids from request query string or external sources (db, elastic search).
     */
    const SEARCH = 'quick_search.search';

    /**
     * Fallback event if elastic search is disabled or some error was happened.
     */
    const SEARCH_FALLBACK = 'quick_search.search_fallback';

    /**
     * Loads data for deferred ids, related entities and then validates view permissions.
     */
    const POST_SEARCH = 'quick_search.post_search';

    /**
     * Called before returning a response, using to sort data.
     */
    const FINISH = 'quick_search.finish';
}
