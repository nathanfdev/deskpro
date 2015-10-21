<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataService;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;

class FiltersDataService extends AbstractDataService
{
    /**
     * @param int|null|Filter $filter
     *
     * @return Filter|null
     */
    public function getFilter($filter)
    {
        $filters_repo = $this->getFilterRepo();

        // this method lets me have an in memory cache, so that
        // if this method is called more than once with the same
        // input during this request, we use our cached version
        // if its not cached, we generate it with the closure
        return $this->generateAndCache(
            array(
                'getFilter',
                $filter,
            ),
            function () use ($filters_repo, $filter) {
                if (!$filter) { // we need some input
                    return;
                }

                if ($filter instanceof Filter) { // you already have what you seek
                    return $filter;
                }

                return $filters_repo->find($filter);
            }
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $em = $this->em;

        return $this->generateAndCache(
            array(
                'getFiltersPager',
            ),
            function () use ($em) {
                $qb = $em->createQueryBuilder();

                $qb->select('f')
                    ->from('App:TicketFilter', 'f');

                return $qb->getQuery()->getResult();
            }
        );
    }

    /**
     * @return FilterRepository
     */
    public function getFilterRepo()
    {
        return $this->em->getRepository('App:TicketFilter');
    }
}
