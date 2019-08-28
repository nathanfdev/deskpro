<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use DeskPRO\Bundle\AppBundle\Entity\AbstractApproval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractBaseApproval
 *
 * Approval superclass used for actual approvals. Contains generic state for responses, etc.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\ApprovalRepository")
 * @ORM\Table(name="approvals")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="dtype", type="string", length=60)
 * @ORM\DiscriminatorMap({
 *   "ticket_approval" = "TicketApproval",
 * })
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Approval
 */
abstract class AbstractBaseApproval extends AbstractApproval
{
    /**
     * Approval statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Maximum number of approvers assigned to an approval
     */
    const APPROVERS_MAX = 100;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=60, nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    protected $status = self::STATUS_PENDING;

    /**
     * @var ApprovalTemplate|null
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate")
     * @ORM\JoinColumn(name="template_id", nullable=true, onDelete="SET NULL")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     * @JMS\Accessor(getter="getTemplateId")
     */
    protected $template;

    /**
     * @var ArrayCollection|ApprovalResponse[]
     *
     * @ORM\OneToMany(
     *     targetEntity="DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse",
     *     mappedBy="approval",
     *     cascade={"persist"},
     * )
     * @ORM\OrderBy({"createdAt"="ASC"})
     */
    protected $responses;

    /**
     * @var int[] Person IDs @see Application\DeskPRO\Entity\Person
     *
     * @ORM\Column(name="approvers", type="json_array", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("array<integer>")
     */
    protected $approvers = [];

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_approved_response_at", type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    protected $lastApprovedResponseAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_reject_response_at", type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    protected $lastRejectResponseAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="completed_at", type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    protected $completedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="cancelled_at", type="datetime", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    protected $cancelledAt;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->responses = new ArrayCollection();
    }

    /**
     * Create a new approval from a given template
     *
     * @param ApprovalTemplate $template
     * @return AbstractBaseApproval
     * @throws \Exception
     */
    public static function createFromTemplate(ApprovalTemplate $template)
    {
        $approval = new static();

        $approval->setType($template->getType());
        $approval->setName($template->getName());
        $approval->setRequiredApprovals($template->getRequiredApprovals());
        $approval->setRequiredRejections($template->getRequiredRejections());
        $approval->setCanApproversViewSubject($template->canApproversViewSubject());
        $approval->setActionsOnCreate($template->getActionsOnCreate());
        $approval->setActionsOnPartialApprovalResponse($template->getActionsOnPartialApprovalResponse());
        $approval->setActionsOnPartialRejectionResponse($template->getActionsOnPartialRejectionResponse());
        $approval->setActionsOnCancel($template->getActionsOnCancel());
        $approval->setActionsOnApproved($template->getActionsOnApproved());
        $approval->setActionsOnRejected($template->getActionsOnRejected());

        $approverCriteria = $template->getApproverCriteria();

        if (!$approverCriteria->canChooseApprovers()) {
            foreach ($approverCriteria->getAgents() as $agentId) {
                $approval->addApprover($agentId);
            }

            foreach ($approverCriteria->getUsers() as $userId) {
                $approval->addApprover($userId);
            }
        }

        $approval->setTemplate($template);

        return $approval;
    }

    /**
     * Determine if this approval is complete and produce an outcome after each response is given
     *
     * @param ApprovalResponse $lastResponse The last approval response to be provided by a user
     * @return string|null Approval status ("approved" or "rejected"), NULL for no change (still pending)
     */
    protected function determineOutcome(ApprovalResponse $lastResponse)
    {
        $requiredApprovals = $this->getRequiredApprovals();
        $requiredRejections = $this->getRequiredRejections();

        if (count($this->getApprovers()) === $requiredApprovals && 0 === $requiredRejections) {
            $requiredRejections = 1;
        }

        if (count($this->getApproveResponses()) >= $requiredApprovals) {
            return self::STATUS_APPROVED;
        } elseif (count($this->getRejectResponses()) >= $requiredRejections) {
            return self::STATUS_REJECTED;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->setModelField('status', $status);

        return $this;
    }

    /**
     * Cancel this approval
     *
     * @return self
     * @throws \Exception
     */
    public function cancel()
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException('Cannot cancel approval if it is already completed or cancelled');
        }

        $this->setStatus(self::STATUS_CANCELLED);
        $this->markCancelledAt();

        return $this;
    }

    /**
     * @return ApprovalTemplate|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return int
     */
    public function getTemplateId()
    {
        return $this->template->getId();
    }

    /**
     * @param ApprovalTemplate|null $template
     * @return self
     */
    public function setTemplate(ApprovalTemplate $template = null)
    {
        $this->setModelField('template', $template);

        return $this;
    }

    /**
     * @return ApprovalResponse[]|ArrayCollection
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * Get approved responses
     *
     * @return ArrayCollection|ApprovalResponse[]
     */
    public function getApproveResponses()
    {
        return $this->responses->filter(function (ApprovalResponse $response) {
            return $response->isApproved();
        });
    }

    /**
     * Get reject responses
     *
     * @return ArrayCollection|ApprovalResponse[]
     */
    public function getRejectResponses()
    {
        return $this->responses->filter(function (ApprovalResponse $response) {
            return $response->isRejected();
        });
    }

    /**
     * @param ApprovalResponse $response
     * @return self
     * @throws \Exception
     */
    public function addResponse(ApprovalResponse $response)
    {
        if ($this->isStatus(self::STATUS_CANCELLED)) {
            throw new \DomainException('Cannot add a response to a cancelled approval');
        }

        // Assert that the approver is allowed to respond
        $this->assertApproverIsInListOfApprovers($response);

        $response->setApproval($this);

        // Assert that this approver has not responded before
        $this->assertApproverNotRespondedBefore($response);

        // Mark timestamps
        if ($response->isApproved()) {
            $this->markLastApprovedResponseAt();
        }
        if ($response->isRejected()) {
            $this->markLastRejectResponseAt();
        }

        $this->responses->add($response);

        // If we're not complete yet, try to determine outcome
        if (!self::isCompletionStatus($this->status)) {
            if (self::isCompletionStatus($outcomeStatus = $this->determineOutcome($response))) {
                $this->setStatus($outcomeStatus);
                $this->markCompletedAt();
            }
        }

        return $this;
    }

    /**
     * @return int[] Person IDs @see Application\DeskPRO\Entity\Person
     */
    public function getApprovers()
    {
        return $this->approvers;
    }

    /**
     * @param int $approver Person ID @see Application\DeskPRO\Entity\Person
     * @return self
     */
    public function addApprover($approver)
    {
        if (count($this->approvers) >= self::APPROVERS_MAX) {
            throw new \DomainException(
                sprintf('Cannot add more than %d approvers to an approval', self::APPROVERS_MAX)
            );
        }

        $this->setModelField(
            'approvers',
            array_merge($this->approvers, [(int) $approver])
        );

        return $this;
    }

    /**
     * @param int $approver
     */
    public function removeApprover($approver)
    {
        // NoOp, approvers cannot be removed once added
    }

    /**
     * @return \DateTime|null
     */
    public function getLastApprovedResponseAt()
    {
        return $this->lastApprovedResponseAt;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return self::isCompletionStatus($this->status);
    }

    /**
     * @param string|array $status
     * @return bool
     */
    public function isStatus($status)
    {
        return self::isOfStatus(
            $this->status,
            is_array($status) ? $status : [$status]
        );
    }

    /**
     * @return self
     * @throws \Exception
     */
    protected function markLastApprovedResponseAt()
    {
        $this->setModelField(
            'lastApprovedResponseAt',
            new \DateTime()
        );

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastRejectResponseAt()
    {
        return $this->lastRejectResponseAt;
    }

    /**
     * @return self
     * @throws \Exception
     */
    protected function markLastRejectResponseAt()
    {
        $this->setModelField(
            'lastRejectResponseAt',
            new \DateTime()
        );

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @return self
     * @throws \Exception
     */
    protected function markCompletedAt()
    {
        $this->setModelField(
            'completedAt',
            new \DateTime()
        );

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCancelledAt()
    {
        return $this->cancelledAt;
    }

    /**
     * @return self
     * @throws \Exception
     */
    protected function markCancelledAt()
    {
        $this->setModelField(
            'cancelledAt',
            new \DateTime()
        );

        return $this;
    }

    /**
     * Asserts that an approver has not responded to this approval before
     *
     * @param ApprovalResponse $response
     */
    private function assertApproverNotRespondedBefore(ApprovalResponse $response)
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('approver', $response->getApprover()))
        ;

        if ($this->responses->matching($criteria)->count()) {
            throw new \DomainException(
                sprintf('Approver, %s, has responded to this approval before', (string) $response->getApprover())
            );
        }
    }

    /**
     * @param ApprovalResponse $response
     */
    private function assertApproverIsInListOfApprovers(ApprovalResponse $response)
    {
        $approver = $response->getApprover();

        if (!in_array($approver->getId(), $this->approvers)) {
            throw new \DomainException(
                sprintf('%s is not listed as an approver for this approval', (string) $approver)
            );
        }
    }

    /**
     * Is a status considered complete
     *
     * @param string $status
     * @return bool
     */
    private static function isCompletionStatus($status)
    {
        return self::isOfStatus($status, [self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }

    /**
     * @param string $status
     * @param array $statuses
     * @return bool
     */
    private static function isOfStatus($status, array $statuses)
    {
        return in_array($status, $statuses);
    }
}
