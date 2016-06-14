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

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\DeskPRO\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Interface DeskPROReaderInterface.
 */
interface DeskPROReaderInterface
{
    /**
     * Returns users count.
     *
     * @param int $min_id
     *
     * @return int
     */
    public function getUsersCount($min_id = 0);

    /**
     * Returns tickets count.
     *
     * @param int $min_id
     *
     * @return int
     */
    public function getTicketsCount($min_id = 0);

    /**
     * Returns a collection of people.
     *
     * @param int $limit
     * @param int $min_id
     *
     * @return Entity\Person[]
     */
    public function findUsers($limit, $min_id = 0);

    /**
     * Returns a collection of people by criteria.
     *
     * @param Criteria $criteria
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findUsersByCriteria(Criteria $criteria);

    /**
     * Returns a collection of tickets.
     *
     * @param int $limit
     * @param int $min_id
     *
     * @return ArrayCollection|Entity\Ticket[]
     */
    public function findTickets($limit, $min_id = 0);

    /**
     * @param Entity\Blob $blob
     *
     * @return null|string
     */
    public function getBlobData(Entity\Blob $blob);

    /**
     * Returns a collection of ticket custom field def.
     *
     * @return ArrayCollection|Entity\CustomDefTicket[]
     */
    public function findCustomDefTickets();

    /**
     * Returns a collection of person custom field def.
     *
     * @return ArrayCollection|Entity\CustomDefPerson[]
     */
    public function findCustomDefPeople();

    /**
     * Returns a collection of organization custom field def.
     *
     * @return ArrayCollection|Entity\CustomDefOrganization[]
     */
    public function findCustomDefOrganizations();

    /**
     * Returns a collection of article custom field def.
     *
     * @return ArrayCollection|Entity\CustomDefArticle[]
     */
    public function findCustomDefArticles();

    /**
     * Returns a collection of feedback custom field def.
     *
     * @return ArrayCollection|Entity\CustomDefFeedback[]
     */
    public function findCustomDefFeedback();
}
