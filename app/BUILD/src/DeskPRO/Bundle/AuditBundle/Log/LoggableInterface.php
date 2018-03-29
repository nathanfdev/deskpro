<?php

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
