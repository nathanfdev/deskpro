<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketViewDataService.
 */
class TicketViewDataService extends AbstractDataService
{
    /**
     * @var CustomFieldManager
     */
    private $customFieldManager;

    /**
     * @var TicketLayoutFactory
     */
    private $ticketLayoutFactory;

    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $brandAwareSettings;

    /**
     * @var CustomFieldUtil
     */
    private $customFieldUtil;

    /**
     * Constructor.
     *
     * @param EntityManager              $em
     * @param CustomFieldManager         $customFieldManager
     * @param TicketLayoutFactory        $ticketLayoutFactory
     * @param Translate                  $translate
     * @param BrandAwareSettingsResolver $brandAwareSettings
     * @param CustomFieldUtil            $customFieldUtil
     */
    public function __construct(
        EntityManager              $em,
        CustomFieldManager         $customFieldManager,
        TicketLayoutFactory        $ticketLayoutFactory,
        Translate                  $translate,
        BrandAwareSettingsResolver $brandAwareSettings,
        CustomFieldUtil            $customFieldUtil
    ) {
        parent::__construct($em);

        $this->customFieldManager  = $customFieldManager;
        $this->ticketLayoutFactory = $ticketLayoutFactory;
        $this->translate           = $translate;
        $this->brandAwareSettings  = $brandAwareSettings;
        $this->customFieldUtil     = $customFieldUtil;
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketView
     */
    public function getUserTicketView(Ticket $ticket)
    {
        // no cache here because it's unlikely to be called more than once per request.
        // if it is, it's probably better to make sure it's an up to date view.

        $view = new TicketView($ticket);

        $full_layout = $this->ticketLayoutFactory->getLayoutForView($ticket->getDepartment());
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
            $defId    = $layout_field->getFieldId();
            switch ($layout_field->getFieldType()) {
                case FormFields::DEPARTMENT:
                    $view->addProperty(
                        $field_id,
                        CustomDefAbstract::TYPE_CHOICE,
                        $this->translate->phrase('user.tickets.fields_department'),
                        $ticket->getDepartment() ? $ticket->getDepartment()->getUserTitle() : '',
                        $layout_field->isVisibleOnViewAlways()
                    );
                    break;
                case FormFields::CATEGORY:
                    if ($this->hasSetting('core.use_ticket_category')) {
                        $view->addProperty(
                            $field_id,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->translate->phrase('user.tickets.fields_category'),
                            $ticket->getCategory(),
                            $layout_field->isVisibleOnViewAlways()
                        );
                    }
                    break;
                case FormFields::PRODUCT:
                    if ($this->hasSetting('core.use_product')) {
                        $view->addProperty(
                            $field_id,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->translate->phrase('user.tickets.fields_product'),
                            $ticket->getProduct(),
                            $layout_field->isVisibleOnViewAlways()
                        );
                    }
                    break;
                case FormFields::PRIORITY:
                    if ($this->hasSetting('core.use_ticket_priority')) {
                        $view->addProperty(
                            $field_id,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->translate->phrase('user.tickets.fields_priority'),
                            $ticket->getPriority(),
                            $layout_field->isVisibleOnViewAlways()
                        );
                    }
                    break;
                case FormFields::TICKET_FIELD:
                    /** @var \Application\DeskPRO\Entity\CustomDefTicket $fieldDef */
                    if (!$fieldDef = $this->customFieldManager->getCustomTicketFieldById($defId)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    if ($fieldDef->isDisplayType()) {
                        // ignore display fields on 'view'
                        break;
                    }

                    /* @var \Application\DeskPRO\Entity\CustomDataTicket $data */
                    $data = $this->customFieldUtil->getCustomDataForField($fieldDef, $ticket->getCustomData());
                    $this->addCustomDataProperty($view, $field_id, $fieldDef, $data, $layout_field->isVisibleOnViewAlways());
                    break;
                case FormFields::ORG_FIELD:
                    $organization = $ticket->getOrganization();
                    if (!$organization instanceof Organization) {
                        break;
                    }
                    /* @var \Application\DeskPRO\Entity\CustomDefOrganization $field_def */
                    if (!$fieldDef = $this->customFieldManager->getCustomOrganizationFieldById($defId)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    if ($fieldDef->isDisplayType()) {
                        // ignore display fields on 'view'
                        break;
                    }

                    /* @var \Application\DeskPRO\Entity\CustomDataOrganization $data */
                    $data = $this->customFieldUtil->getCustomDataForField($fieldDef, $organization->getCustomData());
                    $this->addCustomDataProperty($view, $field_id, $fieldDef, $data, $layout_field->isVisibleOnViewAlways());
                    break;
                case FormFields::USER_FIELD:
                    /* @var \Application\DeskPRO\Entity\CustomDefPerson $fieldDef */
                    if (!$fieldDef = $this->customFieldManager->getCustomPersonFieldById($defId)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    if ($fieldDef->isDisplayType()) {
                        // ignore display fields on 'view'
                        break;
                    }

                    /* @var \Application\DeskPRO\Entity\CustomDataPerson $data */
                    $data = $this->customFieldUtil->getCustomDataForField($fieldDef, $ticket->person->getCustomData());
                    $this->addCustomDataProperty($view, $field_id, $fieldDef, $data, $layout_field->isVisibleOnViewAlways());
                    break;
                case FormFields::CUSTOM_FIELD: // per-user custom fields
                    $value = null;

                    /* @var \Application\DeskPRO\Entity\CustomFieldDefinition $fieldDef */
                    $fieldDef = $this->em->getRepository(CustomFieldDefinition::class)->find($defId);
                    if ($fieldDef) {
                        /* @var CustomFieldData[] $data */
                        /** @var \Application\DeskPRO\EntityRepository\CustomFieldData $repository */
                        $repository = $this->em->getRepository(CustomFieldData::class);

                        $data  = $repository->getFieldData($fieldDef, $ticket);
                        $value = implode(', ', array_map(function (CustomFieldData $customData) {
                            return $customData->getDefinition()->getTitle();
                        }, $data));

                        $view->addProperty(
                            $field_id,
                            CustomDefAbstract::TYPE_CHOICE,
                            $fieldDef->getTitle(),
                            $value,
                            $layout_field->isVisibleOnViewAlways()
                        );
                    }
                    break;
            }
        }

        return $view;
    }

    /**
     * @param TicketView        $view
     * @param int               $fieldId
     * @param CustomDefAbstract $def
     * @param mixed             $data
     * @param bool              $isAlwaysVisible
     *
     * @return bool|null|string
     */
    private function addCustomDataProperty(TicketView $view, $fieldId, CustomDefAbstract $def, $data, $isAlwaysVisible)
    {
        if (is_array($data)) {
            $value = array_map(function ($data) use ($def) {
                return $data ? $this->customFieldUtil->getValueForCustomFormField($def, $data) : null;
            }, $data);
            $value = implode(', ', $value);
        } else {
            $value = $data ? $this->customFieldUtil->getValueForCustomFormField($def, $data) : null;
        }
        if (!$value && $def->isRadio() && $def->getOption('none_choice')) {
            $value = $def->getOption('none_choice_title') ?: 'None';
        }

        $view->addProperty(
            $fieldId,
            $def->getWidgetType(),
            $def->getTitle(),
            (string) $value,
            $isAlwaysVisible,
            $def->getOption('clickable_links')
        );

        return $value;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    private function hasSetting($name)
    {
        return (bool) $this->brandAwareSettings->getSetting($name);
    }
}
