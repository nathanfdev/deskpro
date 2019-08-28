<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use Application\DeskPRO\Tickets\Actions\SendTicketApprovalEmail;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use DeskPRO\Bundle\AppBundle\Entity\AbstractApproval;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalType
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\ApprovalTemplateRepository")
 * @ORM\Table(name="approval_templates")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class ApprovalTemplate extends AbstractApproval
{
    /**
     * @var ApproverCriteria
     *
     * @ORM\Column(name="approval_criteria", type="dp_json_obj", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverCriteria")
     */
    protected $approverCriteria;

    /**
     * @return ApproverCriteria
     */
    public function getApproverCriteria()
    {
        return $this->approverCriteria;
    }

    /**
     * @param ApproverCriteria $approverCriteria
     * @return self
     */
    public function setApproverCriteria(ApproverCriteria $approverCriteria)
    {
        $this->setModelField('approverCriteria', $approverCriteria);

        return $this;
    }

    /**
     * Creates the default email actions for this template if template
     * is to be used for ticket approvals
     *
     * @return void
     */
    public function addDefaultSendTicketApprovalEmailActions()
    {
        $action = new SendTicketApprovalEmail([
            'send_to_owner' => false,
            'send_to_approvers' => true,
            'from_name' => 'helpdesk_name',
            'from_account' => 0,
            'headers' => [],
        ]);

        $this->getActionsOnCreate()->addUniqueAction($action);
        $this->getActionsOnCancel()->addUniqueAction($action);
        $this->getActionsOnPartialApprovalResponse()->addUniqueAction($action);
        $this->getActionsOnPartialRejectionResponse()->addUniqueAction($action);
        $this->getActionsOnApproved()->addUniqueAction($action);
        $this->getActionsOnRejected()->addUniqueAction($action);
    }
}
