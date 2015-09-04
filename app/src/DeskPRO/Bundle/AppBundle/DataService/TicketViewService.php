<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\PortalBundle\CustomField\Context\CustomFieldTicketContext;
use DeskPRO\Bundle\PortalBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\PortalBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager;

class TicketViewService extends AbstractDataService
{
    /**
     * @var FormFieldManager
     */
    private $form_field_manager;

    /**
     * @var TicketLayoutFactory
     */
    private $ticket_layout_factory;

    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var CustomPerFieldManager
     */
    private $custom_per_field_manager;

    public function __construct(
        FormFieldManager $form_field_manager,
        TicketLayoutFactory $ticket_layout_factory,
        Translate $translate,
        CustomPerFieldManager $custom_per_field_manager
    )
    {
        $this->form_field_manager    = $form_field_manager;
        $this->ticket_layout_factory = $ticket_layout_factory;
        $this->translate             = $translate;
        $this->custom_per_field_manager = $custom_per_field_manager;
    }

    public function getUserTicketView(Ticket $ticket)
    {
        // no cache here
        // unlikely to be called more than once per request. if it is, it's probably better to make sure it's an up to date view.

        $view         = new TicketView();
        $view->ticket = $ticket;

        $display_attributes = array();
        $layout             = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->department)->user_layout;

        /** @var \Application\DeskPro\TicketLayout\LayoutField $layout_field */
        foreach ($layout as $layout_field) {
            switch ($layout_field->getFieldType()) {
                case FormFields::DEPARTMENT:
                    $view->attribute_list[$this->translate->phrase('user.tickets.fields_department')] = (string) $ticket->department;
                    break;
                case FormFields::CATEGORY:
                    if ($ticket->category) {
                        $view->attribute_list[$this->translate->phrase('user.tickets.fields_category')] = (string) $ticket->category;
                    }
                    break;
                case FormFields::PRODUCT:
                    if ($ticket->product) {
                        $view->attribute_list[$this->translate->phrase('user.tickets.fields_product')] = (string) $ticket->product;
                    }
                    break;
                case FormFields::PRIORITY:
                    if ($ticket->priority) {
                        $view->attribute_list[$this->translate->phrase('user.tickets.fields_priority')] = (string) $ticket->priority;
                    }
                    break;
                case FormFields::TICKET_FIELD:
                    if ($layout_field->isVisibleOnView()) {
                        /** @var \Application\DeskPRO\Entity\CustomDefTicket $field_def */
                        $field_def = $this->form_field_manager->getCustomTicketFieldById($layout_field->getFieldId());
                        /* @var \Application\DeskPRO\Entity\CustomDataTicket $data */
                        if ($data = $ticket->getCustomDataForField($field_def)) {
                            $value                                        = $this->getValueForCustomFormField($field_def, $data);
                            $view->attribute_list[$field_def->getTitle()] = $value;
                        }
                    }
                    break;
                case FormFields::ORG_FIELD:
                    if ($layout_field->isVisibleOnView()) {
                        if (!$ticket->getOrganization() instanceof Organization) {
                            break;
                        }

                        /** @var \Application\DeskPRO\Entity\CustomDefOrganization $field_def */
                        $field_def = $this->form_field_manager->getCustomOrganizationFieldById($layout_field->getFieldId());
                        /* @var \Application\DeskPRO\Entity\CustomDataOrganization $data */
                        if ($data = $ticket->getOrganization()->getCustomDataForField($field_def)) {
                            $value = $this->getValueForCustomFormField($field_def, $data);
                            $view->attribute_list[$field_def->getTitle()] = $value;
                        }
                    }
                    break;
                case FormFields::USER_FIELD:
                    if ($layout_field->isVisibleOnView()) {
                        /** @var \Application\DeskPRO\Entity\CustomDefPerson $field_def */
                        $field_def = $this->form_field_manager->getCustomPersonFieldById($layout_field->getFieldId());
                        /* @var \Application\DeskPRO\Entity\CustomDataPerson $data */
                        if ($data = $ticket->person->getCustomDataForField($field_def)) {
                            $value = $this->getValueForCustomFormField($field_def, $data);
                            $view->attribute_list[$field_def->getTitle()] = $value;
                        }
                    }
                    break;
                case FormFields::CUSTOM_FIELD:
                    if ($layout_field->isVisibleOnView()) {
                        $context = new CustomFieldTicketContext($ticket);

                        /** @var \Application\DeskPRO\Entity\CustomFieldDefinition $field_def */
                        if (!$field_def = $this->custom_per_field_manager->getCustomPerFieldDefinition(
                            $layout_field->getFieldId(),
                            $context
                        )) {
                            break;
                        }

                        /* @var \Application\DeskPRO\Entity\CustomFieldData $data */
                        if ($data = $this->custom_per_field_manager->getCustomPerFieldData($field_def, $context)) {
                            if ($selected = $this->findSelectedCustomPerFieldChoice($field_def, $context, $data)) {
                                if (is_array($selected)) {
                                    $value = implode(', ', array_map(function($choice_def) {
                                        return $choice_def->getTitle();
                                    },$selected));
                                } else {
                                    $value = $selected->getTitle();
                                }
                                $view->attribute_list[$field_def->getTitle()] = $value;
                            }
                        }
                    }
                    break;
            }
        }

        return $view;
    }

    /**
     * @param $field_def
     * @param $data
     *
     * @return bool|string
     */
    protected function getValueForCustomFormField($field_def, $data)
    {
        $value = '';
        switch ($field_def->getHandlerClass()) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Date':
                try {
                    $datetime = new \DateTime($data->getData());
                    $value = date('F j, Y', $datetime->getTimestamp());
                } catch (\Exception $e) {
                    $value = '';
                }
                break;
            case 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime':
                try {
                    $datetime = new \DateTime($data->getData());
                    $value = date('F j, Y, g:i a', $datetime->getTimestamp());
                } catch (\Exception $e) {
                    $value = '';
                }
                break;
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Toggle':
                if ($data->getData() == 1) {
                    $value = $field_def->getOption('label_text') ?: 'Checked';
                } else {
                    $value = 'None';
                }
                break;
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
            case 'Application\\DeskPRO\\CustomFields\\Handler\\TextArea':
                $value = $data->getData();
                break;
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
                if (!$data->value) {
                    $ids = explode(',', $data->input);
                } else {
                    $ids = array($data->value);
                }
                $selected = array();
                foreach ($ids as $id) {
                    $selected_field = $field_def->getChildById($id);
                    $selected[]     = $selected_field->title;
                }
                $value = implode(', ', $selected);
                break;
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden':
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Display':
            default:
                $value = '';
                break;
        }

        return $value;
    }

    /**
     * @param $field_def
     * @param $context
     * @param $data
     * @return \Application\DeskPRO\Entity\CustomFieldDefinition|null
     */
    public function findSelectedCustomPerFieldChoice(CustomFieldDefinition $field_def, $context, $data)
    {
        $choices = $this->custom_per_field_manager->getCustomPerFieldChoices($field_def, $context);

        if ($field_def->getOption('multiple', false)) {
            $values = explode(',',$data->input);
            return array_filter($choices, function($choice_def) use ($values) {
                return in_array($choice_def->id, $values);
            });
        } else {
            $value = $data->value;
            foreach ($choices as $choice_def) {
                if ($choice_def->id == $value) {
                    return $choice_def;
                }
            }
        }
    }
}
