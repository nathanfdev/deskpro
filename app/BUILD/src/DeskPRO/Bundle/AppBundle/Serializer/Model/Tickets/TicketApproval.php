<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse as ApprovalResponseEntity;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval as TicketApprovalEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketApproval.
 */
class TicketApproval
{
    /**
     * ID.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * Approval name.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $name;

    /**
     * Approval description.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $description;

    /**
     * Approval type.
     *
     * @var ApprovalType
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalType>")
     */
    private $type;

    /**
     * Ticket.
     *
     * @var \Application\DeskPRO\Entity\Ticket
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Ticket>")
     */
    private $ticket;

    /**
     * Status.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $status;

    /**
     * Status name.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $statusName;

    /**
     * Approval template.
     *
     * @var ApprovalTemplate|null
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate>")
     */
    private $template;

    /**
     * Approval responses.
     *
     * @var ApprovalResponseEntity[]
     *
     * @JMS\Type("array<entity<DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse>>")
     */
    private $responses;

    /**
     * Approvers.
     *
     * @var Person[]
     *
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     */
    private $approvers;

    /**
     * Created at.
     *
     * @var \DateTimeInterface
     *
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * Completed date/time.
     *
     * @var \DateTimeInterface
     *
     * @JMS\Type("DateTime")
     */
    private $completedAt;

    /**
     * Cancelled date/time.
     *
     * @var \DateTimeInterface
     *
     * @JMS\Type("DateTime")
     */
    private $cancelledAt;

    /**
     * Cancelled date/time.
     *
     * @var Person
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    private $cancelledBy;

    /**
     * Last approved response date/time.
     *
     * @var \DateTimeInterface
     *
     * @JMS\Type("DateTime")
     */
    private $lastApprovedResponseAt;

    /**
     * Last rejected response date/time.
     *
     * @var \DateTimeInterface
     *
     * @JMS\Type("DateTime")
     */
    private $lastRejectedResponseAt;

    /**
     * Number of approve responses.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $approvedResponsesCount;

    /**
     * Number of reject responses.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $rejectedResponsesCount;

    /**
     * Number of required approvals.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $requiredApprovals;

    /**
     * Number of required rejections.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $requiredRejections;

    /**
     * Number of approvers.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $approversCount;

    /**
     * Number of approvers waiting to respond.
     *
     * @var Person[]
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Person>>")
     */
    private $approversPendingResponse;

    /**
     * Created by.
     *
     * @var Person
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    private $createdBy;

    /**
     * Has the recipient responded to the approval?
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $hasRecipientResponded;

    /**
     * Can the approvers see the subject (ticket)?
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $canApproversViewSubject;

    /**
     * @param TicketApprovalEntity $ticketApproval
     *
     * @return TicketApproval
     */
    public static function createFromEntity(TicketApprovalEntity $ticketApproval)
    {
        $model = new self();

        $model->setId($ticketApproval->getId());
        $model->setType($ticketApproval->getType());
        $model->setName($ticketApproval->getName());
        $model->setDescription($ticketApproval->getDescription());
        $model->setTicket($ticketApproval->getTicket());
        $model->setStatus($ticketApproval->getStatus());
        $model->setStatusName($ticketApproval->getStatusName());
        $model->setTemplate($ticketApproval->getTemplate());
        $model->setResponses($ticketApproval->getResponses()->toArray());
        $model->setApprovers($ticketApproval->getApprovers()->toArray());
        $model->setCreatedAt($ticketApproval->getCreatedAt());
        $model->setCompletedAt($ticketApproval->getCompletedAt());
        $model->setCancelledAt($ticketApproval->getCancelledAt());
        $model->setCancelledBy($ticketApproval->getCancelledBy());
        $model->setLastApprovedResponseAt($ticketApproval->getLastApprovedResponseAt());
        $model->setLastRejectedResponseAt($ticketApproval->getLastRejectResponseAt());
        $model->setApprovedResponsesCount($ticketApproval->getApprovedResponsesCount());
        $model->setRejectedResponsesCount($ticketApproval->getRejectedResponsesCount());
        $model->setRequiredApprovals($ticketApproval->getRequiredApprovals());
        $model->setRequiredRejections($ticketApproval->getRequiredRejections());
        $model->setApproversCount($ticketApproval->getApproversCount());
        $model->setApproversPendingResponse($ticketApproval->getApproversPendingResponse()->toArray());
        $model->setCreatedBy($ticketApproval->getCreatedBy());
        $model->setCanApproversViewSubject($ticketApproval->canApproversViewSubject());

        return $model;
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
     * @return TicketApproval
     */
    public function setId($id)
    {
        $this->id = $id;

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
     *
     * @return TicketApproval
     */
    public function setType(ApprovalType $type)
    {
        $this->type = $type;

        return $this;
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
     *
     * @return TicketApproval
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return TicketApproval
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \Application\DeskPRO\Entity\Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param \Application\DeskPRO\Entity\Ticket $ticket
     *
     * @return TicketApproval
     */
    public function setTicket(\Application\DeskPRO\Entity\Ticket $ticket)
    {
        $this->ticket = $ticket;

        return $this;
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
     *
     * @return TicketApproval
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        return $this->statusName;
    }

    /**
     * @param string $statusName
     *
     * @return TicketApproval
     */
    public function setStatusName($statusName)
    {
        $this->statusName = $statusName;

        return $this;
    }

    /**
     * @return ApprovalTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param ApprovalTemplate $template
     *
     * @return TicketApproval
     */
    public function setTemplate(ApprovalTemplate $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return ApprovalResponseEntity[]
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param ApprovalResponseEntity[] $responses
     *
     * @return TicketApproval
     */
    public function setResponses(array $responses)
    {
        $this->responses = $responses;

        return $this;
    }

    /**
     * @return Person[]
     */
    public function getApprovers()
    {
        return $this->approvers;
    }

    /**
     * @param Person[] $approvers
     *
     * @return TicketApproval
     */
    public function setApprovers(array $approvers)
    {
        $this->approvers = $approvers;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return TicketApproval
     */
    public function setCreatedAt(\DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * @param \DateTimeInterface $completedAt
     *
     * @return TicketApproval
     */
    public function setCompletedAt(\DateTimeInterface $completedAt = null)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCancelledAt()
    {
        return $this->cancelledAt;
    }

    /**
     * @param \DateTimeInterface $cancelledAt
     *
     * @return TicketApproval
     */
    public function setCancelledAt(\DateTimeInterface $cancelledAt = null)
    {
        $this->cancelledAt = $cancelledAt;

        return $this;
    }

    /**
     * @return Person
     */
    public function getCancelledBy()
    {
        return $this->cancelledBy;
    }

    /**
     * @param Person $cancelledBy
     *
     * @return TicketApproval
     */
    public function setCancelledBy(Person $cancelledBy = null)
    {
        $this->cancelledBy = $cancelledBy;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastApprovedResponseAt()
    {
        return $this->lastApprovedResponseAt;
    }

    /**
     * @param \DateTimeInterface $lastApprovedResponseAt
     *
     * @return TicketApproval
     */
    public function setLastApprovedResponseAt(\DateTimeInterface $lastApprovedResponseAt = null)
    {
        $this->lastApprovedResponseAt = $lastApprovedResponseAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastRejectedResponseAt()
    {
        return $this->lastRejectedResponseAt;
    }

    /**
     * @param \DateTimeInterface $lastRejectedResponseAt
     *
     * @return TicketApproval
     */
    public function setLastRejectedResponseAt(\DateTimeInterface $lastRejectedResponseAt = null)
    {
        $this->lastRejectedResponseAt = $lastRejectedResponseAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getApprovedResponsesCount()
    {
        return $this->approvedResponsesCount;
    }

    /**
     * @param int $approvedResponsesCount
     *
     * @return TicketApproval
     */
    public function setApprovedResponsesCount($approvedResponsesCount)
    {
        $this->approvedResponsesCount = $approvedResponsesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRejectedResponsesCount()
    {
        return $this->rejectedResponsesCount;
    }

    /**
     * @param int $rejectedResponsesCount
     *
     * @return TicketApproval
     */
    public function setRejectedResponsesCount($rejectedResponsesCount)
    {
        $this->rejectedResponsesCount = $rejectedResponsesCount;

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
     *
     * @return TicketApproval
     */
    public function setRequiredApprovals($requiredApprovals)
    {
        $this->requiredApprovals = $requiredApprovals;

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
     *
     * @return TicketApproval
     */
    public function setRequiredRejections($requiredRejections)
    {
        $this->requiredRejections = $requiredRejections;

        return $this;
    }

    /**
     * @return int
     */
    public function getApproversCount()
    {
        return $this->approversCount;
    }

    /**
     * @param int $approversCount
     *
     * @return TicketApproval
     */
    public function setApproversCount($approversCount)
    {
        $this->approversCount = $approversCount;

        return $this;
    }

    /**
     * @return Person[]
     */
    public function getApproversPendingResponse()
    {
        return $this->approversPendingResponse;
    }

    /**
     * @param Person[] $approversPendingResponse
     *
     * @return TicketApproval
     */
    public function setApproversPendingResponse(array $approversPendingResponse)
    {
        $this->approversPendingResponse = $approversPendingResponse;

        return $this;
    }

    /**
     * @return Person
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param Person $createdBy
     *
     * @return TicketApproval
     */
    public function setCreatedBy(Person $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHasRecipientResponded()
    {
        return $this->hasRecipientResponded;
    }

    /**
     * @param bool $hasRecipientResponded
     *
     * @return TicketApproval
     */
    public function setHasRecipientResponded($hasRecipientResponded)
    {
        $this->hasRecipientResponded = $hasRecipientResponded;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanApproversViewSubject()
    {
        return $this->canApproversViewSubject;
    }

    /**
     * @param bool $canApproversViewSubject
     *
     * @return TicketApproval
     */
    public function setCanApproversViewSubject($canApproversViewSubject)
    {
        $this->canApproversViewSubject = $canApproversViewSubject;

        return $this;
    }
}
