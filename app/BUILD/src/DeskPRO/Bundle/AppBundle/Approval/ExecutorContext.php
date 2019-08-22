<?php

namespace DeskPRO\Bundle\AppBundle\Approval;

use Application\DeskPRO\Tickets\AbstractExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;

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

    /**
     * @var AbstractBaseApproval
     */
    private $approval;

    /**
     * @var ApprovalResponse|null
     */
    private $approvalResponse;

    /**
     * @return AbstractBaseApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }

    /**
     * @param AbstractBaseApproval $approval
     * @return ExecutorContext
     */
    public function setApproval(AbstractBaseApproval $approval)
    {
        $this->approval = $approval;

        return $this;
    }

    /**
     * @return ApprovalResponse|null
     */
    public function getApprovalResponse()
    {
        return $this->approvalResponse;
    }

    /**
     * @param ApprovalResponse|null $approvalResponse
     * @return ExecutorContext
     */
    public function setApprovalResponse(ApprovalResponse $approvalResponse = null)
    {
        $this->approvalResponse = $approvalResponse;

        return $this;
    }
}
