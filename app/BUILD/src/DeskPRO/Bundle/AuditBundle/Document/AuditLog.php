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

namespace DeskPRO\Bundle\AuditBundle\Document;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Test.
 *
 * @ODM\Document()
 */
class AuditLog  extends AuditLogEntity implements EntityInterface
{
    /**
     * @ODM\Id(strategy="INCREMENT")
     *
     * @var int
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    protected $action;

    /**
     * @ODM\Field(type="date")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    protected $performerName;

    /**
     * @ODM\Field(type="int")
     *
     * @var int
     */
    protected $performerId;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    protected $objectName;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    protected $objectType;

    /**
     * @ODM\Field(type="int")
     *
     * @var int
     */
    protected $objectId;

    /**
     * @ODM\Field(type="string")
     *
     * @var string
     */
    protected $description;

    /**
     * @ODM\Field(type="int")
     *
     * @var int
     */
    protected $apiKey;

    /**
     * @ODM\EmbedOne(targetDocument="DeskPRO\Bundle\AuditBundle\Document\AuditLogData")
     *
     * @var AuditLogData
     */
    protected $data;

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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

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
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setData(AuditLogData $data)
    {
        $this->data = $data;

        return $this;
    }
}
