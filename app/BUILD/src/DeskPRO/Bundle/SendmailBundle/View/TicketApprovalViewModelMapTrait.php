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
        ExecutorContext::EVENT_ON_CREATE => Model\TicketApprovalCreate::class,
        ExecutorContext::EVENT_ON_APPROVED => Model\TicketApprovalApproved::class,
        ExecutorContext::EVENT_ON_REJECTED => Model\TicketApprovalRejected::class,
        ExecutorContext::EVENT_ON_CANCEL => Model\TicketApprovalCancel::class,
        ExecutorContext::EVENT_ON_PARTIAL_APPROVAL_RESPONSE => Model\TicketApprovalPartialApprovalResponse::class,
        ExecutorContext::EVENT_ON_PARTIAL_REJECTION_RESPONSE => Model\TicketApprovalPartialRejectionResponse::class,
    ];

    /**
     * @param string $event
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getViewModelByTicketApprovalEvent($event)
    {
        if (!isset(self::$ticketApprovalEventToViewModelMap[$event])) {
            throw new \InvalidArgumentException(
                sprintf('Cannot find ticket approval view model for event [%s]', $event)
            );
        }

        return self::$ticketApprovalEventToViewModelMap[$event];
    }
}
