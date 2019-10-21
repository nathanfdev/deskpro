<?php

namespace DeskPRO\Bundle\SendmailBundle\View;

use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\SendmailBundle\View\Model;

/**
 * Trait TicketApprovalViewModelMapTrait
 *
 * @package DeskPRO\Bundle\SendmailBundle\View
 */
trait TicketApprovalViewModelMapTrait
{
    /**
     * @var array
     */
    private static $ticketApprovalEventToViewModelMap = [
        'owner' => [
            ExecutorContext::EVENT_ON_CREATE => Model\TicketApprovalOwnerCreate::class,
            ExecutorContext::EVENT_ON_APPROVED => Model\TicketApprovalOwnerApproved::class,
            ExecutorContext::EVENT_ON_REJECTED => Model\TicketApprovalOwnerRejected::class,
            ExecutorContext::EVENT_ON_CANCEL => Model\TicketApprovalOwnerCancel::class,
            ExecutorContext::EVENT_ON_PARTIAL_APPROVAL_RESPONSE => Model\TicketApprovalOwnerPartialApprovalResponse::class,
            ExecutorContext::EVENT_ON_PARTIAL_REJECTION_RESPONSE => Model\TicketApprovalOwnerPartialRejectionResponse::class,
        ],
        'approver' => [
            ExecutorContext::EVENT_ON_CREATE => Model\TicketApprovalApproverCreate::class,
            ExecutorContext::EVENT_ON_APPROVED => Model\TicketApprovalApproverApproved::class,
            ExecutorContext::EVENT_ON_REJECTED => Model\TicketApprovalApproverRejected::class,
            ExecutorContext::EVENT_ON_CANCEL => Model\TicketApprovalApproverCancel::class,
            ExecutorContext::EVENT_ON_PARTIAL_APPROVAL_RESPONSE => Model\TicketApprovalApproverPartialApprovalResponse::class,
            ExecutorContext::EVENT_ON_PARTIAL_REJECTION_RESPONSE => Model\TicketApprovalApproverPartialRejectionResponse::class,
        ],
    ];

    /**
     * @param string $event
     * @param bool   $isOwner
     * @return string
     */
    protected function getViewModelByTicketApprovalEvent($event, $isOwner)
    {
        $type = $isOwner
            ? 'owner'
            : 'approver'
        ;

        if (!isset(self::$ticketApprovalEventToViewModelMap[$type][$event])) {
            throw new \InvalidArgumentException(
                sprintf('Cannot find ticket approval view model for event [%s][%s]', $type, $event)
            );
        }

        return self::$ticketApprovalEventToViewModelMap[$type][$event];
    }
}
