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
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\AppBundle\DataService;


use Application\AppBundle\Model\TicketView;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Translate\Translate;
use Application\FormBundle\Form\FormFieldManager;
use Application\FormBundle\FormFields;
use Application\FormBundle\TicketLayout\TicketLayoutFactory;

class TicketViewService
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

    public function __construct(FormFieldManager $form_field_manager, TicketLayoutFactory $ticket_layout_factory, Translate $translate)
    {
        $this->form_field_manager = $form_field_manager;
        $this->ticket_layout_factory = $ticket_layout_factory;
        $this->translate = $translate;
    }

    public function getUserTicketView(Ticket $ticket)
    {
        $view = new TicketView();
        $view->ticket = $ticket;

        $display_attributes = array();
        $layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->id)->user_layout;

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
                        /** @var \Application\DeskPRO\Entity\CustomDataTicket $data */
                        if ($data = $ticket->getCustomDataForField($field_def)) {
                            $value = $this->getValueForCustomFormField($field_def, $data);
                            $view->attribute_list[$field_def->getTitle()] = $value;
                        }
                    }
                    break;
                case FormFields::USER_FIELD:
                    if ($layout_field->isVisibleOnView()) {
                        /** @var \Application\DeskPRO\Entity\CustomDefPerson $field_def */
                        $field_def = $this->form_field_manager->getCustomPersonFieldById($layout_field->getFieldId());
                        /** @var \Application\DeskPRO\Entity\CustomDataPerson $data */
                        if ($data = $ticket->person->getCustomDataForField($field_def)) {
                            $value = $this->getValueForCustomFormField($field_def, $data);
                            $view->attribute_list[$field_def->getTitle()] = $value;
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
     * @return bool|string
     */
    protected function getValueForCustomFormField($field_def, $data)
    {
        $value = '';
        switch ($field_def->getHandlerClass()) {
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Date':
                $datetime = new \DateTime($data->getData());
                $value = date('F j, Y', $datetime->getTimestamp());
                break;
            case 'Application\\DeskPRO\\CustomFields\\Handler\\DateTime':
                $datetime = new \DateTime($data->getData());
                $value = date('F j, Y, g:i a', $datetime->getTimestamp());
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
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Hidden':
            case 'Application\\DeskPRO\\CustomFields\\Handler\\Display':
            default:
                $value = '';
                break;
        }
        return $value;
    }
}
