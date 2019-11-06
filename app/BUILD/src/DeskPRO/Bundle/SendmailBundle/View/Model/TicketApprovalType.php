<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketApproval;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketApprovalType
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
abstract class TicketApprovalType extends EmailBaseType
{
    /**
     * Either "user" or "admin".
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $recipientType;

    /**
     * Ticket.
     *
     * @var Ticket
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
     */
    protected $ticket;

    /**
     * Approval.
     *
     * @var TicketApproval
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketApproval")
     */
    protected $approval;

    /**
     * Approval Response.
     *
     * @var ApprovalResponse|null
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse")
     */
    protected $approvalResponse;

    /**
     * Recipient person.
     *
     * @var Person
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     */
    protected $recipient;

    /**
     * Is recipient the ticket owner?
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $isOwner;

    /**
     * Has the recipient responded?
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $hasRecipientResponded;

    /**
     * URL to approve.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $approveUrl;

    /**
     * URL to reject.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $rejectUrl;

    /**
     * All approval responses.
     *
     * @var array
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\ApprovalResponse>")
     */
    protected $allResponses;

    /**
     * TicketApprovalType constructor.
     *
     * @param string $recipientType
     * @param Ticket $ticket
     * @param TicketApproval $approval
     * @param Person $recipient
     * @param bool $isOwner
     * @param bool $hasRecipientResponded
     * @param $approveUrl
     * @param $rejectUrl
     * @param ApprovalResponse|null $approvalResponse The current approval response (for partial completion)
     * @param ApprovalResponse[] $allResponses
     */
    public function __construct(
        $recipientType,
        Ticket $ticket,
        TicketApproval $approval,
        Person $recipient,
        $isOwner,
        $hasRecipientResponded,
        $approveUrl,
        $rejectUrl,
        ApprovalResponse $approvalResponse = null,
        array $allResponses = []
    )
    {
        $this->recipientType = $recipientType;
        $this->ticket = $ticket;
        $this->approval = $approval;
        $this->approvalResponse = $approvalResponse;
        $this->recipient = $recipient;
        $this->isOwner = $isOwner;
        $this->approveUrl = $approveUrl;
        $this->rejectUrl = $rejectUrl;
        $this->hasRecipientResponded = $hasRecipientResponded;
        $this->allResponses = $allResponses;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate()
    {
        return sprintf(parent::getTemplate(), $this->recipientType);
    }

    /**
     * @return string
     */
    public function getRecipientType()
    {
        return $this->recipientType;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return TicketApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }

    /**
     * @return ApprovalResponse|null
     */
    public function getApprovalResponse()
    {
        return $this->approvalResponse;
    }

    /**
     * @return Person
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return bool
     */
    public function isOwner()
    {
        return $this->isOwner;
    }

    /**
     * @return bool
     */
    public function isHasRecipientResponded()
    {
        return $this->hasRecipientResponded;
    }

    /**
     * @return string
     */
    public function getApproveUrl()
    {
        return $this->approveUrl;
    }

    /**
     * @return string
     */
    public function getRejectUrl()
    {
        return $this->rejectUrl;
    }

    /**
     * @return array
     */
    public function getAllResponses()
    {
        return $this->allResponses;
    }

    /**
     * @param array $allResponses
     */
    public function setAllResponses(array $allResponses)
    {
        $this->allResponses = $allResponses;
    }
}
