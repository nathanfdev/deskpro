<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\ImportBundle\Model;

/**
 * Exporting entity interface.
 *
 * Interface EntityInterface
 */
interface PrimaryImportModelInterface extends OidAwareModelInterface
{
    /**
     * Returns raw data.
     *
     * @return array
     */
    public function getRawData();

    /**
     * Set raw data.
     *
     * @param array $raw_data
     */
    public function setRawData($raw_data);

    /**
     * Set entity oid.
     *
     * @param int|string $oid
     *
     * @return $this
     */
    public function setOid($oid);

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function setOidPrefix($prefix);

    /**
     * @return string
     */
    public function getOidPrefix();
}
