<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\CustomFields\Handler\Choice;
use Application\DeskPRO\CustomFields\Handler\Date;
use Application\DeskPRO\CustomFields\Handler\DateTime;
use Application\DeskPRO\CustomFields\Handler\Display;
use Application\DeskPRO\CustomFields\Handler\Hidden;
use Application\DeskPRO\CustomFields\Handler\Text;
use Application\DeskPRO\CustomFields\Handler\Textarea;
use Application\DeskPRO\CustomFields\Handler\Toggle;
use Application\DeskPRO\Entity\CustomDataAbstract;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\PortalBundle\CustomField\Context\CustomFieldTicketContext;
use DeskPRO\Bundle\PortalBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\PortalBundle\Form\FormFields;
use Doctrine\ORM\EntityManager;

class TicketViewDataService extends AbstractDataService
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
        EntityManager $em,
        FormFieldManager $form_field_manager,
        TicketLayoutFactory $ticket_layout_factory,
        Translate $translate,
        CustomPerFieldManager $custom_per_field_manager
    ) {
        parent::__construct($em);
        $this->form_field_manager       = $form_field_manager;
        $this->ticket_layout_factory    = $ticket_layout_factory;
        $this->translate                = $translate;
        $this->custom_per_field_manager = $custom_per_field_manager;
    }

    public function getUserTicketView(Ticket $ticket)
    {
        // no cache here because it's unlikely to be called more than once per request.
        // if it is, it's probably better to make sure it's an up to date view.

        $view = new TicketView($ticket);

        $full_layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->getDepartment());
        $layout      = $full_layout->getUserLayout();

        /** @var \Application\DeskPro\TicketLayout\LayoutField $layout_field */
        foreach ($layout as $layout_field) {
            if (!$layout_field->isVisibleOnView()) {
                // if the field is not visible on view, don't show it
                continue;
            }
            if ($layout_field->hasCriteria() && !$layout_field->getCriteria()->isTicketMatch($ticket)) {
                // field has criteria that this ticket does not match, dont add it to the view
                continue;
            }
            $field_id = $layout_field->getId();
            $def_id   = $layout_field->getFieldId();
            switch ($layout_field->getFieldType()) {
                case FormFields::DEPARTMENT:
                    $view->addProperty(
                        $field_id,
                        $this->translate->phrase('user.tickets.fields_department'),
                        $ticket->getDepartment(),
                        $layout_field->isVisibleOnViewAlways()
                    );
                    break;
                case FormFields::CATEGORY:
                    $view->addProperty(
                        $field_id,
                        $this->translate->phrase('user.tickets.fields_category'),
                        $ticket->getCategory(),
                        $layout_field->isVisibleOnViewAlways()
                    );
                    break;
                case FormFields::PRODUCT:
                    $view->addProperty(
                        $field_id,
                        $this->translate->phrase('user.tickets.fields_product'),
                        $ticket->getProduct(),
                        $layout_field->isVisibleOnViewAlways()
                    );
                    break;
                case FormFields::PRIORITY:
                    $view->addProperty(
                        $field_id,
                        $this->translate->phrase('user.tickets.fields_priority'),
                        $ticket->getPriority(),
                        $layout_field->isVisibleOnViewAlways()
                    );
                    break;
                case FormFields::TICKET_FIELD:
                    /** @var \Application\DeskPRO\Entity\CustomDefTicket $field_def */
                    if (!$field_def = $this->form_field_manager->getCustomTicketFieldById($def_id)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    /* @var \Application\DeskPRO\Entity\CustomDataTicket $data */
                    $data = $ticket->getCustomDataForField($field_def);
                    $this->addCustomDataProperty($view, $field_id, $field_def, $data, $layout_field->isVisibleOnViewAlways());
                    break;
                case FormFields::ORG_FIELD:
                    $organization = $ticket->getOrganization();
                    if (!$organization instanceof Organization) {
                        break;
                    }
                    /** @var \Application\DeskPRO\Entity\CustomDefOrganization $field_def */
                    if (!$field_def = $this->form_field_manager->getCustomOrganizationFieldById($def_id)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    /* @var \Application\DeskPRO\Entity\CustomDataOrganization $data */
                    $data = $organization->getCustomDataForField($field_def);
                    $this->addCustomDataProperty($view, $field_id, $field_def, $data, $layout_field->isVisibleOnViewAlways());
                    break;
                case FormFields::USER_FIELD:
                    /** @var \Application\DeskPRO\Entity\CustomDefPerson $field_def */
                    if (!$field_def = $this->form_field_manager->getCustomPersonFieldById($def_id)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    /* @var \Application\DeskPRO\Entity\CustomDataPerson $data */
                    $data = $ticket->person->getCustomDataForField($field_def);
                    $this->addCustomDataProperty($view, $field_id, $field_def, $data, $layout_field->isVisibleOnViewAlways());
                    break;
                case FormFields::CUSTOM_FIELD: // per-user custom fields
                    $context = new CustomFieldTicketContext($ticket);

                    /** @var \Application\DeskPRO\Entity\CustomFieldDefinition $field_def */
                    $field_def = $this->custom_per_field_manager->getCustomPerFieldDefinition(
                        $def_id,
                        $context
                    );
                    if (!$field_def) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }

                    /* @var \Application\DeskPRO\Entity\CustomFieldData $data */
                   $value = null;
                    if ($data = $this->custom_per_field_manager->getCustomPerFieldData($field_def, $context)) {
                        if ($selected = $this->findSelectedCustomPerFieldChoice($field_def, $context, $data)) {
                            if (is_array($selected)) {
                                $value = implode(', ', array_map(function ($choice_def) {
                                    /* @var \Application\DeskPRO\Entity\CustomFieldDefinition $choice_def */
                                    return $choice_def->getTitle();
                                }, $selected));
                            } else {
                                $value = $selected->getTitle();
                            }
                        }
                    }
                    $view->addProperty(
                        $field_id,
                        $field_def->getTitle(),
                        $value,
                        $layout_field->isVisibleOnViewAlways()
                    );
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
    protected function getValueForCustomFormField(CustomDefAbstract $field_def, CustomDataAbstract $data)
    {
        switch ($field_def->getHandlerClass()) {
            case Date::class:
                try {
                    $datetime = new \DateTime($data->getData());
                    $value    = date('F j, Y', $datetime->getTimestamp());
                } catch (\Exception $e) {
                    $value = '';
                }
                break;
            case DateTime::class:
                try {
                    $datetime = new \DateTime($data->getData());
                    $value    = date('F j, Y, g:i a', $datetime->getTimestamp());
                } catch (\Exception $e) {
                    $value = '';
                }
                break;
            case Toggle::class:
                if ($data->getData() == 1) {
                    $value = $field_def->getOption('label_text') ?: 'Checked';
                } else {
                    $value = 'None';
                }
                break;
            case Text::class:
            case Textarea::class:
                $value = $data->getData();
                break;
            case Choice::class:
                if (!$data->getValue()) {
                    $ids = explode(',', $data->getInput());
                } else {
                    $ids = array($data->getValue());
                }
                $selected = array();
                foreach ($ids as $id) {
                    if ($selected_field = $field_def->getChildById($id)) {
                        $selected[] = $selected_field->getTitle();
                    }
                }
                $value = implode(', ', $selected);
                break;
            case Hidden::class:
                $value = $data->getInput();
                break;
            case Display::class:
                $value = $field_def->getHtmlOption();
                break;
            default:
                $value = null;
                break;
        }

        return $value;
    }

    /**
     * @param $field_def
     * @param $context
     * @param $data
     *
     * @return \Application\DeskPRO\Entity\CustomFieldDefinition|null
     */
    public function findSelectedCustomPerFieldChoice(CustomFieldDefinition $field_def, $context, $data)
    {
        $choices = $this->custom_per_field_manager->getCustomPerFieldChoices($field_def, $context);

        if ($field_def->getOption('multiple', false)) {
            $values = explode(',', $data->input);

            return array_filter($choices, function ($choice_def) use ($values) {
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

    /**
     * @param TicketView $view
     * @param $field_id
     * @param CustomDefAbstract $field_def
     * @param $data
     *
     * @return bool|null|string
     */
    public function addCustomDataProperty(TicketView $view, $field_id, CustomDefAbstract $field_def, $data, $is_always_visible)
    {
        $value = $data ? $this->getValueForCustomFormField($field_def, $data) : null;

        $view->addProperty(
            $field_id,
            $field_def->getTitle(),
            (string) $value,
            $is_always_visible
        );

        return $value;
    }
}
