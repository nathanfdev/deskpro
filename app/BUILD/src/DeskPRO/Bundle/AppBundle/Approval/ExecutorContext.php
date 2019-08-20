<?php

namespace DeskPRO\Bundle\AppBundle\Approval;

use Application\DeskPRO\Tickets\AbstractExecutorContext;

/**
 * Class ExecutorContext
 *
 * @package DeskPRO\Bundle\AppBundle\Approval
 */
class ExecutorContext extends AbstractExecutorContext
{
    /**
     * Approval events
     */
    const EVENT_ON_CREATE = 'create';
    const EVENT_ON_PARTIAL_APPROVAL_RESPONSE = 'partial_approval_response';
    const EVENT_ON_PARTIAL_REJECTION_RESPONSE = 'partial_rejection_response';
    const EVENT_ON_CANCEL = 'cancel';
    const EVENT_ON_APPROVED = 'approved';
    const EVENT_ON_REJECTED = 'rejected';
}
