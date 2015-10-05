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

namespace Application\ImportBundle\Generator\Exporter\Parser;

use DateTime;

/**
 * Exporter batch configuration interface.
 *
 * Interface BatchConfigInterface
 */
interface BatchConfigInterface
{
    /**
     * Returns batch id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set batch id.
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Returns exporter type.
     *
     * @return string
     */
    public function getExporterType();

    /**
     * Returns date created.
     *
     * @return DateTime
     */
    public function getDateCreated();

    /**
     * Set date created.
     *
     * @param DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(DateTime $date_created = null);

    /**
     * Returns date modified.
     *
     * @return DateTime
     */
    public function getDateModified();

    /**
     * Set date modified.
     *
     * @param DateTime $date_modified
     *
     * @return $this
     */
    public function setDateModified(DateTime $date_modified = null);

    /**
     * Set has remaining.
     *
     * @param bool $has_remaining
     *
     * @return $this
     */
    public function setHasRemaining($has_remaining);

    /**
     * Has remaining?
     *
     * @return bool
     */
    public function getHasRemaining();

    /**
     * Convert to array.
     *
     * @return array
     */
    public function toArray();
}
