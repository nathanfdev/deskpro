<?php

namespace DeskPRO\Bundle\AuditBundle\Entity;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\AuditBundle\Document\AuditLogData;
use DeskPRO\Bundle\AuditBundle\Log\LoggableInterface;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Test.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="audit_logs",
 *     indexes={
 *      @ORM\Index(name="performer_idx", columns={"performer_id", "date_created"}),
 *      @ORM\Index(name="date_created_idx", columns={"date_created"}),
 *      @ORM\Index(name="apikey_idx", columns={"api_key", "date_created"}),
 *      @ORM\Index(name="object_idx", columns={"object_type", "object_id"}),
 *     }
 * )
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("NONE")
 * @JMS\ExclusionPolicy("all")
 */
class AuditLog implements LoggableInterface, NotifyPropertyChanged, EntityInterface
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="bigint")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $action;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="date_created")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\Groups("list")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @ORM\Column(type="string", name="performer_name")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $performerName;

    /**
     * @ORM\Column(type="integer", name="performer_id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $performerId;

    /**
     * @ORM\Column(type="string", name="object_name", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $objectName;

    /**
     * @ORM\Column(type="string", name="object_type", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $objectType;

    /**
     * @ORM\Column(type="integer", name="object_id", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $objectId;

    /**
     * @ORM\Column(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="integer", name="api_key", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $apiKey;

    /**
     * @ORM\Column(type="dp_json_obj")
     *
     * @JMS\Expose()
     * @JMS\Groups("details")
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
    public function setApiKey($apiKey = null)
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
