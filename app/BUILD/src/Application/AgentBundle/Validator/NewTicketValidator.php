<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
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
     * @param array $page_data
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
                if ($field && $field->is_enabled) {
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
                            }

                            $this->addError('ticket.'.$code, array('message' => $str, 'field' => 'ticket_field_' . $field->getId()));
                        }
                    }
                }
                break;

            case 'user_field':
                $field = App::getSystemService('PersonFieldsManager')->getFieldFromId($item->getFieldId());
                if ($field && $field->is_enabled) {
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

                            $this->addError('person.'.$code, ['message' => $str, 'field' => 'person_field_' . $field->getId()]);
                        }
                    }
                }
                break;

            case 'org_field':
                $field = App::getSystemService('OrgFieldsManager')->getFieldFromId($item->getFieldId());
                if ($field && $field->is_enabled) {
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

                            $this->addError('person.'.$code, ['message' => $str, 'field' => 'org_field_' . $field->getId()]);
                        }
                    }
                }
                break;

        }
    }
}
