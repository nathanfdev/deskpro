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
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $recipientType;

    /**
     * @var Ticket
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket")
     */
    protected $ticket;

    /**
     * @var TicketApproval
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketApproval")
     */
    protected $approval;

    /**
     * @var ApprovalResponse|null
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse")
     */
    protected $approvalResponse;

    /**
     * @var Person
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     */
    protected $recipient;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $isOwner;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $hasRecipientResponded;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $approveUrl;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $rejectUrl;

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
     * @param ApprovalResponse|null $approvalResponse
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
        ApprovalResponse $approvalResponse = null
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
}
