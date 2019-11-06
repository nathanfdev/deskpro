<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Tickets\Triggers\TriggerActions;
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
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType>")
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
     * @var TriggerActions
     *
     * @ORM\Column(name="actions_on_create", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     */
    protected $actionsOnCreate;

    /**
     * @var TriggerActions
     *
     * @ORM\Column(name="actions_on_partial_approval_response", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     */
    protected $actionsOnPartialApprovalResponse;

    /**
     * @var TriggerActions
     *
     * @ORM\Column(name="actions_on_partial_rejection_response", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     */
    protected $actionsOnPartialRejectionResponse;

    /**
     * @var TriggerActions
     *
     * @ORM\Column(name="actions_on_cancel", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     */
    protected $actionsOnCancel;

    /**
     * @var TriggerActions
     *
     * @ORM\Column(name="actions_on_approved", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     */
    protected $actionsOnApproved;

    /**
     * @var TriggerActions
     *
     * @ORM\Column(name="actions_on_rejected", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("Application\DeskPRO\Tickets\Triggers\TriggerActions")
     */
    protected $actionsOnRejected;

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
        $this->actionsOnCreate = new TriggerActions();
        $this->actionsOnPartialApprovalResponse = new TriggerActions();
        $this->actionsOnPartialRejectionResponse = new TriggerActions();
        $this->actionsOnCancel = new TriggerActions();
        $this->actionsOnApproved = new TriggerActions();
        $this->actionsOnRejected = new TriggerActions();
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
    public function __toString()
    {
        return $this->getName();
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
        self::assertZeroOrMoreRequiredApprovers($requiredApprovals);

        $this->setModelField('requiredApprovals', (int) $requiredApprovals);

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
        self::assertZeroOrMoreRequiredApprovers($requiredRejections);

        $this->setModelField('requiredRejections', (int) $requiredRejections);

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

    /**
     * @param TriggerActions|null $actionsOnCreate
     * @return AbstractApproval
     */
    public function setActionsOnCreate(TriggerActions $actionsOnCreate = null)
    {
        if ($actionsOnCreate) {
            $this->setModelField('actionsOnCreate', $actionsOnCreate);
        }

        return $this;
    }

    /**
     * @param TriggerActions|null $actionsOnPartialApprovalResponse
     * @return AbstractApproval
     */
    public function setActionsOnPartialApprovalResponse(TriggerActions $actionsOnPartialApprovalResponse = null)
    {
        if ($actionsOnPartialApprovalResponse) {
            $this->setModelField('actionsOnPartialApprovalResponse', $actionsOnPartialApprovalResponse);
        }

        return $this;
    }

    /**
     * @param TriggerActions|null $actionsOnPartialRejectionResponse
     * @return AbstractApproval
     */
    public function setActionsOnPartialRejectionResponse(TriggerActions $actionsOnPartialRejectionResponse = null)
    {
        if ($actionsOnPartialRejectionResponse) {
            $this->setModelField('actionsOnPartialRejectionResponse', $actionsOnPartialRejectionResponse);
        }

        return $this;
    }

    /**
     * @param TriggerActions|null $actionsOnCancel
     * @return AbstractApproval
     */
    public function setActionsOnCancel(TriggerActions $actionsOnCancel = null)
    {
        if ($actionsOnCancel) {
            $this->setModelField('actionsOnCancel', $actionsOnCancel);
        }

        return $this;
    }

    /**
     * @param TriggerActions|null $actionsOnApproved
     * @return AbstractApproval
     */
    public function setActionsOnApproved(TriggerActions $actionsOnApproved = null)
    {
        if ($actionsOnApproved) {
            $this->setModelField('actionsOnApproved', $actionsOnApproved);
        }

        return $this;
    }

    /**
     * @param TriggerActions|null $actionsOnRejected
     * @return AbstractApproval
     */
    public function setActionsOnRejected(TriggerActions $actionsOnRejected = null)
    {
        if ($actionsOnRejected) {
            $this->setModelField('actionsOnRejected', $actionsOnRejected);
        }

        return $this;
    }

    /**
     * @return TriggerActions
     */
    public function getActionsOnCreate()
    {
        return $this->actionsOnCreate;
    }

    /**
     * @return TriggerActions
     */
    public function getActionsOnPartialApprovalResponse()
    {
        return $this->actionsOnPartialApprovalResponse;
    }

    /**
     * @return TriggerActions
     */
    public function getActionsOnPartialRejectionResponse()
    {
        return $this->actionsOnPartialRejectionResponse;
    }

    /**
     * @return TriggerActions
     */
    public function getActionsOnCancel()
    {
        return $this->actionsOnCancel;
    }

    /**
     * @return TriggerActions
     */
    public function getActionsOnApproved()
    {
        return $this->actionsOnApproved;
    }

    /**
     * @return TriggerActions
     */
    public function getActionsOnRejected()
    {
        return $this->actionsOnRejected;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $requiredApprovers
     */
    private static function assertZeroOrMoreRequiredApprovers($requiredApprovers)
    {
        if ($requiredApprovers < 0) {
            throw new \DomainException(
                sprintf('Number of required approvals/rejections must be zero or more, %d given', $requiredApprovers)
            );
        }
    }
}
