<?php

namespace DeskPRO\Bundle\AuditBundle\Log;

use DeskPRO\Bundle\AuditBundle\Document\AuditLogData;

/**
 * Class AuditLog.
 */
class AuditLog
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @var string
     */
    protected $performerName;

    /**
     * @var int
     */
    protected $performerId;

    /**
     * @var string
     */
    protected $objectName;

    /**
     * @var string
     */
    protected $objectType;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $apiKey;

    /**
     * @var AuditLogData
     */
    protected $data;

    /**
     * AuditLog constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return LoggableInterface
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return LoggableInterface
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return LoggableInterface
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getPerformerName()
    {
        return $this->performerName;
    }

    /**
     * @param string $performerName
     *
     * @return LoggableInterface
     */
    public function setPerformerName($performerName)
    {
        $this->performerName = $performerName;

        return $this;
    }

    /**
     * @return int
     */
    public function getPerformerId()
    {
        return $this->performerId;
    }

    /**
     * @param int $performerId
     *
     * @return LoggableInterface
     */
    public function setPerformerId($performerId)
    {
        $this->performerId = $performerId;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return $this->objectName;
    }

    /**
     * @param string $objectName
     *
     * @return LoggableInterface
     */
    public function setObjectName($objectName)
    {
        $this->objectName = $objectName;

        return $this;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     *
     * @return LoggableInterface
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     *
     * @return LoggableInterface
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return LoggableInterface
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param int $apiKey
     *
     * @return LoggableInterface
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return AuditLogData
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param AuditLogData $data
     *
     * @return LoggableInterface
     */
    public function setData(AuditLogData $data)
    {
        $this->data = $data;

        return $this;
    }
}
