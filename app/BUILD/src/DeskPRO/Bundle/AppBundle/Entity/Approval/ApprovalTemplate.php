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
     * @var bool if TRUE then approverSelectionCriteria is used, else selectedApprovers is used
     *
     * @ORM\Column(name="can_choose_approvers", type="boolean")
     *
     * @JMS\Expose
     * @JMS\Type("boolean")
     */
    protected $canChooseApprovers;

    /**
     * @var SelectedApprovers|null
     *
     * @ORM\Column(name="selected_approvers", type="dp_json_obj", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\SelectedApprovers")
     */
    protected $selectedApprovers;

    /**
     * @var ApproverSelectionCriteria|null
     *
     * @ORM\Column(name="approver_selection_criteria", type="dp_json_obj", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverSelectionCriteria")
     */
    protected $approverSelectionCriteria;

    /**
     * @return ApproverSelectionCriteria
     */
    public function getApproverSelectionCriteria()
    {
        return $this->approverSelectionCriteria;
    }

    /**
     * @param ApproverSelectionCriteria $approverSelectionCriteria
     * @return self
     */
    public function setApproverSelectionCriteria(ApproverSelectionCriteria $approverSelectionCriteria)
    {
        $this->setModelField('approverSelectionCriteria', $approverSelectionCriteria);

        return $this;
    }

    /**
     * @return bool
     */
    public function canChooseApprovers()
    {
        return $this->canChooseApprovers;
    }

    /**
     * @param bool $canChooseApprovers
     * @return ApprovalTemplate
     */
    public function setCanChooseApprovers($canChooseApprovers)
    {
        $this->setModelField('canChooseApprovers', (bool) $canChooseApprovers);

        return $this;
    }

    /**
     * @return SelectedApprovers
     */
    public function getSelectedApprovers()
    {
        return $this->selectedApprovers;
    }

    /**
     * @param SelectedApprovers $selectedApprovers
     * @return ApprovalTemplate
     */
    public function setSelectedApprovers(SelectedApprovers $selectedApprovers)
    {
        $this->setModelField('selectedApprovers', $selectedApprovers);

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
