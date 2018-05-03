<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\BlobRestrictionSet;
use Orb\Util\Numbers;
use Orb\Validator\AbstractValidator;

class NewTicketValidator extends AbstractValidator
{
    /** @var array */
    protected $run_validators = [];

    /**
     * @var \Application\AgentBundle\Form\Model\NewTicket
     */
    protected $newticket;

    /**
     * @var array
     */
    protected $layout = [];

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $mock_ticket;

    /**
     * @var bool
     */
    protected $is_resolved = false;

    /**
     * @param Layout $layout
     */
    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Check $value to see if its valid.
     *
     * @param \Application\AgentBundle\Form\Model\NewTicket $newticket
     *
     * @return bool
     */
    protected function checkIsValid($newticket)
    {
        $this->newticket   = $newticket;
        $this->is_resolved = $newticket->status == 'resolved';

        if ($this->layout instanceof LayoutDisplay && ($t = $this->layout->getTicket())) {
            $this->mock_ticket = $t;
        } else {
            $this->mock_ticket = new \Application\DeskPRO\Entity\Ticket(false);
            $this->mock_ticket->_setNoPersist();
            if ($newticket->department_id) {
                $this->mock_ticket->setDepartmentId($newticket->department_id);
            }
            if ($newticket->category_id) {
                $this->mock_ticket->setCategoryId($newticket->category_id);
            }
            if ($newticket->product_id) {
                $this->mock_ticket->setProductId($newticket->product_id);
            }
            if ($newticket->priority_id) {
                $this->mock_ticket->setPriorityId($newticket->priority_id);
            }
            if ($newticket->workflow_id) {
                $this->mock_ticket->setWorkflowId($newticket->workflow_id);
            }
        }

        $this->_traverseItems($this->layout);

        if ($this->errors) {
            return false;
        }

        return true;
    }

    protected function _traverseItems(Layout $layout)
    {
        foreach ($layout as $item) {
            $this->_validateItem($item);
        }
    }

    protected function _validateItem(LayoutField $item)
    {
        if ($item->hasCriteria() && !$item->getCriteria()->isTicketMatch($this->mock_ticket)) {
            return;
        }
        $translator = App::getTranslator();

        switch ($item->getFieldType()) {
            case 'product':
                if (App::getSetting('core.use_product')) {
                    $validator = new \Application\DeskPRO\Validator\GenericCategory([
                        'category_repository' => App::getEntityRepository('DeskPRO:Product'),
                        'allow_none'          => !App::getSetting('core_tickets.field_validation_ticket_prod_agent_required'),
                    ]);
                    if (!$validator->isValid($this->newticket->product_id)) {
                        $this->addError('ticket.product_id', ['message' => 'Select a product', 'field' => 'product']);
                    }
                }
                break;

            case 'category':
                if (App::getSetting('core.use_ticket_category')) {
                    $validator = new \Application\DeskPRO\Validator\GenericCategory([
                        'category_repository' => App::getEntityRepository('DeskPRO:TicketCategory'),
                        'allow_none'          => !App::getSetting('core_tickets.field_validation_ticket_cat_agent_required'),
                    ]);
                    if (!$validator->isValid($this->newticket->category_id)) {
                        $this->addError('ticket.category_id', ['message' => 'Select a category', 'field' => 'category']);
                    }
                }
                break;

            case 'priority':
                if (App::getSetting('core.use_ticket_priority')) {
                    $validator = new \Application\DeskPRO\Validator\TicketPriority([
                        'allow_none' => !App::getSetting('core_tickets.field_validation_ticket_pri_agent_required'),
                    ]);
                    if (!$validator->isValid($this->newticket->priority_id)) {
                        $this->addError('ticket.priority_id', ['message' => 'Select a priority', 'field' => 'priority']);
                    }
                }
                break;

            case 'workflow':
                if (App::getSetting('core.use_ticket_workflow')) {
                    $validator = new \Application\DeskPRO\Validator\TicketWorkflow([
                        'allow_none' => !App::getSetting('core_tickets.field_validation_ticket_work_agent_required'),
                    ]);
                    if (!$validator->isValid($this->newticket->workflow_id)) {
                        $this->addError('ticket.workflow_id', ['message' => 'Select a workflow', 'field' => 'workflow']);
                    }
                }
                break;

            case 'ticket_field':
                $field = App::getSystemService('TicketFieldsManager')->getFieldFromId($item->getFieldId());
                if ($field instanceof CustomDefTicket && $field->isEnabled()) {
                    if ($field->getOption('agent_validation_resolve') && !$this->is_resolved) {
                        // no validation, its only on resolve
                    } else {
                        if ($this->newticket->exist_ticket) {
                            $errors = $field->getHandler()->validateFormData($this->newticket->ticket_fields, HandlerAbstract::CONTEXT_AGENT, ['exist_ticket' => $this->newticket->exist_ticket]);
                        } else {
                            $errors = $field->getHandler()->validateFormData($this->newticket->ticket_fields, HandlerAbstract::CONTEXT_AGENT);
                        }
                        foreach ($errors as $code) {
                            $title = $field->getTitle();
                            $str   = "Please correct $title";
                            $code  = str_replace('field_'.$field->getId().'.', '', $code);

                            if ($translator->hasPhrase('user.error.form_'.$code)) {
                                $str = $translator->phrase('user.error.form_'.$code);
                            }
                            switch ($code) {
                                case 'required':
                                    $str = "$title is required";
                                    break;
                                case 'min_length':
                                    $str = "$title is too short";
                                    break;
                                case 'max_length':
                                    $str = "$title is too long";
                                    break;
                                case 'regex':
                                    $str = "$title is invalid";
                                    break;
                                case BlobRestrictionSet::ACCEPT_SIZE:
                                    $str = $translator->phrase('api.error_codes.'.$code, [
                                        'detail' => Numbers::filesizeDisplay($field->getOption('agent_max_file_size')),
                                    ]);
                                    break;
                                case BlobRestrictionSet::ACCEPT_NOT_ALLOWED_EXTENSION:
                                    $str = $translator->phrase('api.error_codes.'.$code, [
                                        'detail' => implode(',', $field->getOption('agent_not_extensions')),
                                    ]);
                                    break;
                                case BlobRestrictionSet::ACCEPT_NOT_IN_ALLOWED_EXTENSION:
                                    $str = $translator->phrase('api.error_codes.'.$code, [
                                        'detail' => implode(',', $field->getOption('agent_must_extensions')),
                                    ]);
                                    break;
                            }

                            $this->addError('ticket.'.$field->getId().'.'.$code, ['message' => $str, 'field' => 'ticket_field_'.$field->getId()]);
                        }
                    }
                }
                break;

            case 'user_field':
                $field = App::getSystemService('PersonFieldsManager')->getFieldFromId($item->getFieldId());
                if ($field instanceof CustomDefPerson && $field->isEnabled()) {
                    if ($field->getOption('agent_validation_resolve') && !$this->is_resolved) {
                        // no validation, its only on resolve
                    } else {
                        if ($this->newticket->exist_ticket) {
                            $errors = $field->getHandler()->validateFormData($this->newticket->custom_person_fields ?: [], HandlerAbstract::CONTEXT_AGENT, ['exist_ticket' => $this->newticket->exist_ticket]);
                        } else {
                            $errors = $field->getHandler()->validateFormData($this->newticket->custom_person_fields ?: [], HandlerAbstract::CONTEXT_AGENT);
                        }
                        foreach ($errors as $code) {
                            $title = $field->getTitle();
                            $str   = "Please correct $title";
                            $code  = str_replace('field_'.$field->getId().'.', '', $code);
                            switch ($code) {
                                case 'required':
                                    $str = "$title is required";
                                    break;
                                case 'min_length':
                                    $str = "$title is too short";
                                    break;
                                case 'max_length':
                                    $str = "$title is too long";
                                    break;
                                case 'regex':
                                    $str = "$title is invalid";
                                    break;
                            }

                            $this->addError('person.'.$field->getId().'.'.$code, ['message' => $str, 'field' => 'person_field_'.$field->getId()]);
                        }
                    }
                }
                break;

            case 'org_field':
                // make sure the user belongs to an org
                if (!$this->newticket->organization_id) {
                    return;
                }

                $field = App::getSystemService('OrgFieldsManager')->getFieldFromId($item->getFieldId());
                if ($field instanceof CustomDefOrganization && $field->isEnabled()) {
                    if ($field->getOption('agent_validation_resolve') && !$this->is_resolved) {
                        // no validation, its only on resolve
                    } else {
                        if ($this->newticket->exist_ticket) {
                            $errors = $field->getHandler()->validateFormData($this->newticket->custom_org_fields ?: [], HandlerAbstract::CONTEXT_AGENT, ['exist_ticket' => $this->newticket->exist_ticket]);
                        } else {
                            $errors = $field->getHandler()->validateFormData($this->newticket->custom_org_fields ?: [], HandlerAbstract::CONTEXT_AGENT);
                        }
                        foreach ($errors as $code) {
                            $title = $field->getTitle();
                            $str   = "Please correct $title";
                            $code  = str_replace('field_'.$field->getId().'.', '', $code);
                            switch ($code) {
                                case 'required':
                                    $str = "$title is required";
                                    break;
                                case 'min_length':
                                    $str = "$title is too short";
                                    break;
                                case 'max_length':
                                    $str = "$title is too long";
                                    break;
                                case 'regex':
                                    $str = "$title is invalid";
                                    break;
                            }

                            $this->addError('org.'.$field->getId().'.'.$code, ['message' => $str, 'field' => 'org_field_'.$field->getId()]);
                        }
                    }
                }
                break;

        }
    }
}
