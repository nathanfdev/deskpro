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

namespace Application\ImportBundle\Reader\ZenDesk\Request;

/**
 * ZenDesk API request adapter interface.
 *
 * Interface RequestAdapterInterface
 */
interface RequestAdapterInterface
{
    /**
     * Request a batch collection of people.
     *
     * @param array $params
     *
     * @return \stdClass
     */
    public function doPeopleIncrementalExportRequest(array $params = array());

    /**
     * Request a batch collection of people by ids.
     *
     * @param array $params
     *
     * @return \stdClass
     */
    public function doPeopleFindRequest(array $params = array());

    /**
     * Request an organization by id.
     *
     * @param array $params
     *
     * @return \stdClass
     */
    public function doOrganizationFindRequest(array $params = array());

    /**
     * Request a batch collection of tickets.
     *
     * @param array $params
     *
     * @return \stdClass
     */
    public function doTicketsIncrementalExportRequest(array $params = array());

    /**
     * Request a collection of ticket comments.
     *
     * @param array $params
     *
     * @return \stdClass
     */
    public function doTicketCommentsFindAllRequest(array $params = array());
}
