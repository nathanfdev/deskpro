<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket_statuses")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 *
 * @JMS\ExclusionPolicy("ALL")
 * @JMS\AccessorOrder("custom", custom = {"id", "statusType", "statusCode", "sysId", "title", "parent", "displayOrder"})
 *
 * @UniqueEntity("sysId")
 */
class TicketStatus implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const STATUS_TYPE_AWAITING_AGENT = 'awaiting_agent';
    const STATUS_TYPE_AWAITING_USER  = 'awaiting_user';
    const STATUS_TYPE_PENDING        = 'pending';
    const STATUS_TYPE_RESOLVED       = 'resolved';
    const STATUS_TYPE_ARCHIVED       = 'archived';
    const STATUS_TYPE_HIDDEN         = 'hidden';

    const SYS_ID_DELETED = 'deleted';
    const SYS_ID_SPAM    = 'spam';

    /**
     * @return array
     */
    public static function getStatusTypes()
    {
        return [
            self::STATUS_TYPE_AWAITING_AGENT,
            self::STATUS_TYPE_AWAITING_USER,
            self::STATUS_TYPE_PENDING,
            self::STATUS_TYPE_RESOLVED,
            self::STATUS_TYPE_ARCHIVED,
            self::STATUS_TYPE_HIDDEN,
        ];
    }

    /**
     * @param string $statusType
     *
     * @return bool
     */
    public static function isValidStatusType($statusType)
    {
        return in_array($statusType, self::getStatusTypes());
    }

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="status_type", type="string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getStatusTypes")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $statusType;

    /**
     * @ORM\Column(name="sys_id", type="string", nullable=true, unique=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $sysId;

    /**
     * @ORM\Column(name="title", type="string")
     *
     * @Assert\NotBlank()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity="TicketStatus")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * // JMS excluded on purpose
     *
     * @var TicketStatus
     */
    protected $parent = null;

    /**
     * @ORM\Column(name="display_order", type="integer", nullable=true)
     *
     * @Assert\Type("integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $displayOrder;

    /**
     * @var ArrayCollection
     * @JMS\Expose()
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Entity\TicketStatus>")
     */
    protected $children;

    public function __construct($statusType)
    {
        $this->setModelField('statusType', $statusType);
        $this->children = new ArrayCollection();
    }

    /**
     * @return int|null
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
     * @return string
     */
    public function getStatusType()
    {
        return $this->statusType;
    }

    /**
     * @return string
     */
    public function getSysId()
    {
        return $this->sysId;
    }

    /**
     * @param string $sysId
     *
     * @return $this
     */
    public function setSysId($sysId)
    {
        if ($sysId === '') {
            $sysId = null;
        }
        $this->setModelField('sysId', $sysId);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayTitle()
    {
        return $this->getTitle();
    }

    /**
     * @return TicketStatus|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param TicketStatus $status
     *
     * @return $this
     */
    public function setParent(TicketStatus $status = null)
    {
        $this->setModelField('parent', $status);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     *
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->setModelField('displayOrder', $displayOrder);

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Type("string")
     * @JMS\SerializedName("status_code")
     *
     * @return string
     */
    public function getStatusCode()
    {
        $code = $this->statusType;
        if ($this->id) {
            $code .= '.'.$this->id;
        }

        return $code;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection|array $children
     *
     * @return $this
     */
    public function setChildren($children)
    {
        if (is_array($children) && !$children instanceof ArrayCollection) {
            $children = new ArrayCollection($children);
        }

        $this->children = $children;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->sysId == 'deleted';
    }

    /**
     * @return bool
     */
    public function isSpam()
    {
        return $this->sysId == 'spam';
    }
}
