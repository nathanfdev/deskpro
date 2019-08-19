<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalType
 *
 * Low level approval state used in both approval templates and in actual approvals
 *
 * @ORM\MappedSuperclass
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
abstract class AbstractApproval implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="required_approvals", type="integer", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $requiredApprovals = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="required_rejections", type="integer", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $requiredRejections = 1;

    /**
     * @var ApprovalType
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType", inversedBy="approvals")
     * @ORM\JoinColumn(name="type_id", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getTypeId")
     */
    protected $type;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_approvers_view_subject", type="boolean", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    protected $canApproversViewSubject = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    protected $createdAt;

    /**
     * AbstractApproval constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->setModelField('description', $description);

        return $this;
    }

    /**
     * @return int
     */
    public function getRequiredApprovals()
    {
        return $this->requiredApprovals;
    }

    /**
     * @param int $requiredApprovals
     * @return self
     */
    public function setRequiredApprovals($requiredApprovals)
    {
        $this->setModelField('requiredApprovals', $requiredApprovals);

        return $this;
    }

    /**
     * @return int
     */
    public function getRequiredRejections()
    {
        return $this->requiredRejections;
    }

    /**
     * @param int $requiredRejections
     * @return self
     */
    public function setRequiredRejections($requiredRejections)
    {
        $this->setModelField('requiredRejections', $requiredRejections);

        return $this;
    }

    /**
     * @return ApprovalType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param ApprovalType $type
     * @return self
     */
    public function setType(ApprovalType $type)
    {
        $this->setModelField('type', $type);

        return $this;
    }

    /**
     * @return bool
     */
    public function canApproversViewSubject()
    {
        return $this->canApproversViewSubject;
    }

    /**
     * @param bool $canApproversViewSubject
     * @return self
     */
    public function setCanApproversViewSubject($canApproversViewSubject)
    {
        $this->setModelField('canApproversViewSubject', (bool) $canApproversViewSubject);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTypeId()
    {
        return $this->type->getId();
    }
}
