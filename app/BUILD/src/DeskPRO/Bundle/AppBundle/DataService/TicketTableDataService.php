<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Model\TicketColumn;
use DeskPRO\Bundle\AppBundle\Model\TicketColumns;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\PortalBundle\View\Ticket\TicketListTable;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketTableDataService.
 */
class TicketTableDataService extends AbstractDataService
{
    /**
     * @var TicketsDataService
     */
    private $ticket_data_service;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var DepartmentDataService
     */
    private $department_data_service;

    /**
     * @var TicketLayoutFactory
     */
    private $ticketLayoutFactory;

    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $brand_aware_settings;

    public function __construct(
        EntityManager $em,
        TicketsDataService $ticket_data_service,
        LanguageManager $language_manager,
        DepartmentDataService $department_data_service,
        TicketLayoutFactory $ticket_layout_factory,
        CustomFieldManager $form_field_manager,
        BrandAwareSettingsResolver $brand_aware_settings
    ) {
        parent::__construct($em);
        $this->ticket_data_service     = $ticket_data_service;
        $this->language_manager        = $language_manager;
        $this->department_data_service = $department_data_service;
        $this->ticketLayoutFactory     = $ticket_layout_factory;
        $this->fieldManager            = $form_field_manager;
        $this->brand_aware_settings    = $brand_aware_settings;
    }

    public function makeTicketTable(Person $person, Request $request, $ticket_type, $category, $category_title)
    {
        $columns  = $this->makeColumnControl($person);
        $per_page = $this->brand_aware_settings->getSetting('portal.per_page_tickets', null, 50);
        $table    = new TicketListTable($category, $ticket_type, $category_title, $columns, $request, $per_page);
        $table->makePagerUsingDataService($this->ticket_data_service, $person);

        return $table;
    }

    public function makeColumnControl(Person $person)
    {
        $columns = new TicketColumns();

        $this->addStaticColumns($columns);
        $this->addDynamicColumns($columns, $person);

        return $columns;
    }

    protected function phrase($phrase_name, $vars = [])
    {
        return $this->language_manager->phrase($phrase_name, $vars);
    }

    /**
     * @param TicketColumns $columns
     */
    public function addStaticColumns(TicketColumns $columns)
    {
        //$columns->addColumn(
        //    TicketColumn::TYPE_DEPARTMENT_SUBJECT,
        //    $this->phrase('portal.tickets.list_department').' / '.$this->phrase('portal.tickets.list_subject'),
        //    TicketColumn::TYPE_DEPARTMENT_SUBJECT
        //);

        $columns->addColumn(
            TicketColumn::TYPE_SUBJECT,
            $this->phrase('portal.tickets.list_subject'),
            TicketColumn::TYPE_SUBJECT,
            CustomDefAbstract::TYPE_TEXT
        );

        $columns->addColumn(
            TicketColumn::TYPE_DEPARTMENT,
            $this->phrase('portal.tickets.list_department'),
            TicketColumn::TYPE_DEPARTMENT,
            CustomDefAbstract::TYPE_CHOICE
        );

        $columns->addColumn(
            TicketColumn::TYPE_USER,
            $this->phrase('portal.tickets.list_user'),
            TicketColumn::TYPE_USER,
            CustomDefAbstract::TYPE_DISPLAY
        );

        $columns->addColumn(
            TicketColumn::TYPE_AGENT,
            $this->phrase('portal.tickets.list_agent'),
            TicketColumn::TYPE_AGENT,
            CustomDefAbstract::TYPE_DISPLAY
        );

        $columns->addColumn(
            TicketColumn::TYPE_DATE_CREATED,
            $this->phrase('portal.tickets.list_date_created'),
            TicketColumn::TYPE_DATE_CREATED,
            CustomDefAbstract::TYPE_DATETIME
        );

        $columns->addColumn(
            TicketColumn::TYPE_DATE_ACTIVITY,
            $this->phrase('portal.tickets.list_last_action'),
            TicketColumn::TYPE_DATE_ACTIVITY,
            CustomDefAbstract::TYPE_DATETIME
        );

        $columns->addColumn(
            TicketColumn::TYPE_DATE_USER,
            $this->phrase('portal.tickets.list_date_last_user'),
            TicketColumn::TYPE_DATE_USER,
            CustomDefAbstract::TYPE_DATETIME
        );

        $columns->addColumn(
            TicketColumn::TYPE_DATE_AGENT,
            $this->phrase('portal.tickets.list_date_last_agent'),
            TicketColumn::TYPE_DATE_AGENT,
            CustomDefAbstract::TYPE_DATETIME
        );
    }

    /**
     * @param TicketColumns $columns
     * @param Person        $person
     */
    public function addDynamicColumns(TicketColumns $columns, Person $person)
    {
        $deps = $this->department_data_service->getTicketDepartmentsForPerson($person);

        foreach ($deps as $dep) {
            $layout = $this->ticketLayoutFactory->getLayoutForTicketForm($dep);
            $layout = $layout->getUserLayout();
            /** @var LayoutField $field */
            foreach ($layout as $field) {
                if (!$label = $this->getLabelForLayoutField($field)) {
                    continue;
                }

                $column = new TicketColumn(
                    $field->getId(),
                   $label,
                    TicketColumn::TYPE_PROPERTY,
                    $this->getWidgetTypeForLayoutField($field)
                );
                $columns->appendColumn($column);
            }
        }
    }

    /**
     * We filter out lots of fields here, like ticket message, or user email, etc.
     * They are present in the department layout, but they don't belong in the table.
     * For the ones we care about, we make and return a label.
     *
     * @param LayoutField $field
     *
     * @return string|null
     */
    private function getLabelForLayoutField(LayoutField $field)
    {
        $defId = $field->getFieldId();
        if (!$field->isVisibleOnView()) {
            // if the field is not visible on view, don't add it as a column
            return;
        }
        switch ($field->getFieldType()) {
            case FormFields::CATEGORY:
                return $this->phrase('user.tickets.fields_category');
            case FormFields::PRODUCT:
                return $this->phrase('user.tickets.fields_product');
            case FormFields::PRIORITY:
                return $this->phrase('user.tickets.fields_priority');
            case FormFields::TICKET_FIELD:
                /** @var \Application\DeskPRO\Entity\CustomDefTicket $fieldDef */
                if (!$fieldDef = $this->fieldManager->getCustomTicketFieldById($defId)) {
                    // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                    break;
                }

                return $fieldDef->getTitle();
            case FormFields::ORG_FIELD:
                if (!$fieldDef = $this->fieldManager->getCustomOrganizationFieldById($defId)) {
                    // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                    break;
                }

                return $fieldDef->getTitle();
            case FormFields::USER_FIELD:
                /* @var \Application\DeskPRO\Entity\CustomDefPerson $field_def */
                if (!$fieldDef = $this->fieldManager->getCustomPersonFieldById($defId)) {
                    // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                    break;
                }

                return $fieldDef->getTitle();
        }

        return;
    }

    /**
     * @param LayoutField $field
     *
     * @return string|null
     */
    private function getWidgetTypeForLayoutField(LayoutField $field)
    {
        $defId = $field->getFieldId();
        if (!$field->isVisibleOnView()) {
            // if the field is not visible on view, don't add it as a column
            return;
        }
        switch ($field->getFieldType()) {
            case FormFields::CATEGORY:
            case FormFields::PRODUCT:
            case FormFields::PRIORITY:
                return CustomDefAbstract::TYPE_CHOICE;
            case FormFields::TICKET_FIELD:
                /** @var \Application\DeskPRO\Entity\CustomDefTicket $fieldDef */
                if (!$fieldDef = $this->fieldManager->getCustomTicketFieldById($defId)) {
                    // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                    break;
                }

                return $fieldDef->getWidgetType();
            case FormFields::ORG_FIELD:
                if (!$fieldDef = $this->fieldManager->getCustomOrganizationFieldById($defId)) {
                    // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                    break;
                }

                return $fieldDef->getWidgetType();
            case FormFields::USER_FIELD:
                /* @var \Application\DeskPRO\Entity\CustomDefPerson $field_def */
                if (!$fieldDef = $this->fieldManager->getCustomPersonFieldById($defId)) {
                    // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                    break;
                }

                return $fieldDef->getWidgetType();
        }

        return;
    }
}
