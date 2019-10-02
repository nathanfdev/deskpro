<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Entity\AbstractApproval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var array Map of approval statuses to their UI names
     */
    protected static $statusNameMap = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_APPROVED => 'approved',
        self::STATUS_REJECTED => 'rejected',
        self::STATUS_CANCELLED => 'cancelled',
    ];

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
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate>")
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
     * @ORM\OrderBy({"createdAt"="DESC"})
     *
     * @JMS\Expose
     * @JMS\Type("array<entity<DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse>>")
     */
    protected $responses;

    /**
     * @var Person[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinTable(name="approval_approvers",
     *      joinColumns={@ORM\JoinColumn(name="approval_id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="person_id", onDelete="CASCADE")}
     *  )
     *
     * @JMS\Expose
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     */
    protected $approvers;

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
     * @var Person|null
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="cancelled_by", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    protected $cancelledBy;

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->responses = new ArrayCollection();
        $this->approvers = new ArrayCollection();
    }

    /**
     * Is invoked during @see \DeskPRO\Bundle\AppBundle\Approval\ApprovalManager operations to
     * notify changes on associated entities
     *
     * @param EntityManagerInterface $em
     * @return void
     */
    abstract public function notifyAssociationChanges(EntityManagerInterface $em);

    /**
     * Create a new approval from a given template
     *
     * @param EntityManagerInterface $em
     * @param ApprovalTemplate $template
     * @param AbstractBaseApproval|null $prototype
     * @return AbstractBaseApproval
     * @throws \Doctrine\ORM\ORMException
     */
    public static function createFromTemplate(EntityManagerInterface $em, ApprovalTemplate $template, self $prototype = null)
    {
        $approval = $prototype ?: new static();

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

        // If we cannot choose approvers in agent UI, then the users must come from selected approvers object
        if (!$template->canChooseApprovers()) {
            $selectedApprovers = $template->getSelectedApprovers();

            /** @var PersonRepository $personRepo */
            $personRepo = $em->getRepository(Person::class);

            // Get approvers from the implementation of this abstract approval
            foreach ($approval->getExtraApproversWhenCreatingFromTemplate($em, $selectedApprovers) as $extraApprover) {
                $approval->addApprover($extraApprover);
            }

            // Add an organization managers
            if ($selectedApprovers->hasOrganizationManagers()) {
                foreach ($personRepo->getOrganizationManagers() as $orgManager) {
                    $approval->addApprover($orgManager);
                }
            }

            // Add all agents
            if ($selectedApprovers->hasAllAgents()) {
                foreach ($personRepo->getAgents() as $agent) {
                    $approval->addApprover($agent);
                }
            }

            // Add any specific people (agents or users)
            foreach ($selectedApprovers->getPeople() as $personId) {
                $approval->addApprover($em->getReference(Person::class, $personId));
            }
        }

        $approval->setTemplate($template);

        return $approval;
    }

    /**
     * Use the sub class to determine extra approvers from selected approvers object
     *
     * @param EntityManagerInterface $em
     * @param SelectedApprovers $selectedApprovers
     * @return Person[]
     */
    protected function getExtraApproversWhenCreatingFromTemplate(
        EntityManagerInterface $em,
        SelectedApprovers $selectedApprovers
    ) {
        return [];
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

        if ($this->getApproversCount() === $requiredApprovals && 0 === $requiredRejections) {
            $requiredRejections = 1;
        }

        if ($this->getApprovedResponsesCount() >= $requiredApprovals) {
            return self::STATUS_APPROVED;
        }

        if ($this->getRejectedResponsesCount() >= $requiredRejections) {
            return self::STATUS_REJECTED;
        }

        return null;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * @param Person $cancelledBy
     * @return self
     * @throws \Exception
     */
    public function cancel(Person $cancelledBy)
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new \DomainException('Cannot cancel approval if it is already completed or cancelled');
        }

        $this->setModelField('cancelledBy', $cancelledBy);
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
     * @return int
     */
    public function getApprovedResponsesCount()
    {
        return count($this->getApproveResponses());
    }

    /**
     * @return int
     */
    public function getRejectedResponsesCount()
    {
        return count($this->getRejectResponses());
    }

    /**
     * @param Person $person
     * @return bool
     */
    public function hasResponded(Person $person)
    {
        return !$this->responses->filter(function (ApprovalResponse $response) use ($person) {
            return $response->getApprover()->isEqualTo($person);
        })->isEmpty();
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
     * @return Person[]|ArrayCollection
     */
    public function getApprovers()
    {
        return $this->approvers;
    }

    /**
     * @return int
     */
    public function getApproversCount()
    {
        return count($this->getApprovers());
    }

    /**
     * @param Person $approver
     * @return self
     */
    public function addApprover(Person $approver)
    {
        if (count($this->approvers) >= self::APPROVERS_MAX) {
            throw new \DomainException(
                sprintf('Cannot add more than %d approvers to an approval', self::APPROVERS_MAX)
            );
        }

        $this->approvers->add($approver);

        $this->setModelField('approvers', $this->approvers);

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
     * @return int[]
     */
    public function getApproverIds()
    {
        return $this->approvers->map(function (Person $person) {
            return $person->getId();
        })->toArray();
    }

    /**
     * @return \DateTime|null
     */
    public function getLastApprovedResponseAt()
    {
        return $this->lastApprovedResponseAt;
    }

    /**
     * @return ArrayCollection|Person[]
     */
    public function getApproversPendingResponse()
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->notIn('id', $this->responses->map(function (ApprovalResponse $response) {
                return $response->getApprover()->getId();
            })->toArray()))
        ;

        return $this->approvers->matching($criteria);
    }

    /**
     * @param Person $person
     * @return bool
     */
    public function hasApprover(Person $person)
    {
        return !$this->approvers->filter(function (Person $approver) use ($person) {
            return $person->isEqualTo($approver);
        })->isEmpty();
    }

    /**
     * @return Person|null
     */
    public function getCancelledBy()
    {
        return $this->cancelledBy;
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
     * @return string
     */
    public function getStatusName()
    {
        return self::$statusNameMap[$this->status];
    }

    /**
     * @return array
     */
    public static function getStatusNameMap()
    {
        return self::$statusNameMap;
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

        if (!in_array($approver->getId(), $this->getApproverIds())) {
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
