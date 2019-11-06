<?php

namespace Application\DeskPRO\ORM\StateChange;

use DeskPRO\Bundle\AppBundle\Approval\ExecutorContext;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;

/**
 * Class ChangeApproval.
 */
class ChangeApproval implements ChangeInterface
{
    /**
     * @var string
     */
    private $fieldId;

    /**
     * @var AbstractBaseApproval
     */
    private $approval;

    /**
     * @var ExecutorContext
     */
    private $context;

    /**
     * @var ApprovalResponse
     */
    private $latestResponse;

    /**
     * Constructor.
     *
     * @param string               $fieldId
     * @param AbstractBaseApproval $approval
     * @param ExecutorContext      $context
     * @param ApprovalResponse     $latestResponse
     */
    public function __construct($fieldId, AbstractBaseApproval $approval, ExecutorContext $context, ApprovalResponse $latestResponse = null)
    {
        $this->fieldId        = $fieldId;
        $this->approval       = $approval;
        $this->context        = $context;
        $this->latestResponse = $latestResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->fieldId;
    }

    /**
     * {@inheritdoc}
     */
    public function getOld()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getNew()
    {
        $data = [
            'event'                         => $this->context->getEventType(),
            'name'                          => $this->approval->getName(),
            'description'                   => $this->approval->getDescription(),
            'status'                        => $this->approval->getStatus(),
            'status_name'                   => $this->approval->getStatusName(),
            'number_of_approvals'           => count($this->approval->getApproveResponses()),
            'number_of_rejections'          => count($this->approval->getRejectResponses()),
            'number_of_required_approvals'  => $this->approval->getRequiredApprovals(),
            'number_of_required_rejections' => $this->approval->getRequiredRejections(),
        ];

        if ($this->latestResponse) {
            $data['response_vote']      = $this->latestResponse->getVote();
            $data['response_vote_type'] = $this->latestResponse->getVoteType();
            $data['response_message']   = $this->latestResponse->getMessage();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isSame()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isCollection()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isEntity()
    {
        return false;
    }

    /**
     * @return AbstractBaseApproval
     */
    public function getApproval()
    {
        return $this->approval;
    }

    /**
     * @return ExecutorContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ApprovalResponse
     */
    public function getLatestResponse()
    {
        return $this->latestResponse;
    }
}
