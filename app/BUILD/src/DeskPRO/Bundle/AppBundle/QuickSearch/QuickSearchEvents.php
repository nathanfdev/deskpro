<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
