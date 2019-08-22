<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketApprovalType
 *
 * @package DeskPRO\Bundle\SendmailBundle\View\Model
 */
abstract class TicketApprovalType extends EmailBaseType
{
    /**
     * @var Ticket
     *
     * @JMS\Type("Application\DeskPRO\Entity\Ticket")
     */
    protected $ticket;

    /**
     * @var TicketApproval
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval")
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
     * @JMS\Type("Application\DeskPRO\Entity\Person")
     */
    protected $recipient;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $isOwner;

    /**
     * TicketApprovalType constructor.
     *
     * @param Ticket $ticket
     * @param TicketApproval $approval
     * @param Person $recipient
     * @param bool $isOwner
     * @param ApprovalResponse|null $approvalResponse
     */
    public function __construct(
        Ticket $ticket,
        TicketApproval $approval,
        Person $recipient,
        $isOwner,
        ApprovalResponse $approvalResponse = null
    ) {
        $this->ticket = $ticket;
        $this->approval = $approval;
        $this->approvalResponse = $approvalResponse;
        $this->recipient = $recipient;
        $this->isOwner = $isOwner;
    }
}
