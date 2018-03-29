<?php

namespace DeskPRO\Bundle\AuditBundle\Document;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Test.
 *
 * @ODM\Document()
 * @JMS\ExclusionPolicy("all")
 */
class AuditLog extends AuditLogEntity implements EntityInterface
{
    /**
     * @ODM\Id(strategy="INCREMENT")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $action;

    /**
     * @ODM\Field(type="date")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\Groups("list")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $performerName;

    /**
     * @ODM\Field(type="int")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $performerId;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $objectName;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $objectType;

    /**
     * @ODM\Field(type="int")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $objectId;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $description;

    /**
     * @ODM\Field(type="int")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $apiKey;

    /**
     * @ODM\EmbedOne(targetDocument="DeskPRO\Bundle\AuditBundle\Document\AuditLogData")
     * @JMS\Groups("details")
     *
     * @var AuditLogData
     */
    protected $data;
}
