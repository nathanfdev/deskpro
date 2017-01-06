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

namespace DeskPRO\Bundle\AuditBundle\Log;

use DeskPRO\Bundle\AuditBundle\Document\AuditLogData;

interface LoggableInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getAction();

    /**
     * @param mixed $action
     *
     * @return $this
     */
    public function setAction($action);

    /**
     * @return \DateTime
     */
    public function getDateCreated();

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated($dateCreated);

    /**
     * @return string
     */
    public function getPerformerName();

    /**
     * @param string $performerName
     *
     * @return $this
     */
    public function setPerformerName($performerName);

    /**
     * @return int
     */
    public function getPerformerId();

    /**
     * @param int $performerId
     *
     * @return $this
     */
    public function setPerformerId($performerId);

    /**
     * @return string
     */
    public function getObjectName();

    /**
     * @param string $objectName
     *
     * @return $this
     */
    public function setObjectName($objectName);

    /**
     * @return string
     */
    public function getObjectType();

    /**
     * @param string $objectType
     *
     * @return $this
     */
    public function setObjectType($objectType);

    /**
     * @return int
     */
    public function getObjectId();

    /**
     * @param int $objectId
     *
     * @return $this
     */
    public function setObjectId($objectId);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return int
     */
    public function getApiKey();

    /**
     * @param int $apiKey
     *
     * @return $this
     */
    public function setApiKey($apiKey = null);

    /**
     * @return AuditLogData
     */
    public function getData();

    /**
     * @param AuditLogData $data
     *
     * @return $this
     */
    public function setData(AuditLogData $data);
}
