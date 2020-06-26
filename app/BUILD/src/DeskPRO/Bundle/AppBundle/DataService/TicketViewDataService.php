<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\CustomDataOrganization;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPro\TicketLayout\LayoutField;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Model\TicketView;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
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
     * @var DepartmentDataService
     */
    private $departmentDataService;

    /**
     * @var PortalBrandThemeLoader
     */
    private $portalBrandThemeLoader;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param CustomFieldManager $customFieldManager
     * @param TicketLayoutFactory $ticketLayoutFactory
     * @param Translate $translate
     * @param BrandAwareSettingsResolver $brandAwareSettings
     * @param CustomFieldUtil $customFieldUtil
     * @param DepartmentDataService $departmentDataService
     * @param PortalBrandThemeLoader|null $portalBrandThemeLoader
     * @param BrandStack|null $brandStack
     */
    public function __construct(
        EntityManager $em,
        CustomFieldManager $customFieldManager,
        TicketLayoutFactory $ticketLayoutFactory,
        Translate $translate,
        BrandAwareSettingsResolver $brandAwareSettings,
        CustomFieldUtil $customFieldUtil,
        DepartmentDataService $departmentDataService,
        PortalBrandThemeLoader $portalBrandThemeLoader = null,
        BrandStack $brandStack = null
    ) {
        parent::__construct($em);

        $this->customFieldManager     = $customFieldManager;
        $this->ticketLayoutFactory    = $ticketLayoutFactory;
        $this->translate              = $translate;
        $this->brandAwareSettings     = $brandAwareSettings;
        $this->customFieldUtil        = $customFieldUtil;
        $this->departmentDataService  = $departmentDataService;
        $this->portalBrandThemeLoader = $portalBrandThemeLoader;
        $this->brandStack             = $brandStack;
    }

    /**
     * @param Ticket $ticket
     * @param Person|null $person
     *
     * @return TicketView
     */
    public function getUserTicketView(Ticket $ticket, Person $person = null)
    {
        // no cache here because it's unlikely to be called more than once per request.
        // if it is, it's probably better to make sure it's an up to date view.

        $view = new TicketView($ticket);

        $fullLayout = $this->ticketLayoutFactory->getLayoutForView($ticket->getDepartment());
        $layout     = $fullLayout->getUserLayout();

        /** @var LayoutField $layoutField */
        foreach ($layout as $layoutField) {
            if (!$layoutField->isVisibleOnView()) {
                // if the field is not visible on view, don't show it
                continue;
            }
            if ($layoutField->hasCriteria() && !$layoutField->getCriteria()->isTicketMatch($ticket)) {
                // field has criteria that this ticket does not match, dont add it to the view
                continue;
            }
            $fieldId = $layoutField->getId();
            $defId   = $layoutField->getFieldId();
            switch ($layoutField->getFieldType()) {
                case FormFields::DEPARTMENT:
                    $title = '';
                    if ($department = $ticket->getDepartment()) {
                        $title = $department->getUserTitle();

                        if ($parent = $department->getParent()) {
                            $title = $parent->getUserTitle().' / '.$title;
                        }
                    }

                    $departments = $this->departmentDataService->getTicketDepartmentsForPerson($person);
                    if (count($departments) > 1) {
                        $view->addProperty(
                            $fieldId,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->phrase(['user.tickets.fields_department', 'helpcenter.general.department']),
                            $title,
                            $layoutField->isVisibleOnViewAlways()
                        );
                    }

                    break;
                case FormFields::CATEGORY:
                    if ($this->hasSetting('core.use_ticket_category')) {
                        $view->addProperty(
                            $fieldId,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->phrase(['user.tickets.fields_category', 'helpcenter.general.category']),
                            $ticket->getCategory(),
                            $layoutField->isVisibleOnViewAlways()
                        );
                    }

                    break;
                case FormFields::PRODUCT:
                    if ($this->hasSetting('core.use_product')) {
                        $view->addProperty(
                            $fieldId,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->phrase(['user.tickets.fields_product', 'helpcenter.general.product']),
                            $ticket->getProduct(),
                            $layoutField->isVisibleOnViewAlways()
                        );
                    }

                    break;
                case FormFields::PRIORITY:
                    if ($this->hasSetting('core.use_ticket_priority')) {
                        $view->addProperty(
                            $fieldId,
                            CustomDefAbstract::TYPE_CHOICE,
                            $this->phrase(['user.tickets.fields_priority', 'helpcenter.general.priority']),
                            $ticket->getPriority(),
                            $layoutField->isVisibleOnViewAlways()
                        );
                    }

                    break;
                case FormFields::TICKET_FIELD:
                    /** @var CustomDefTicket $fieldDef */
                    if (!$fieldDef = $this->customFieldManager->getCustomTicketFieldById($defId)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    if ($fieldDef->isDisplayType()) {
                        // ignore display fields on 'view'
                        break;
                    }

                    /* @var CustomDataTicket $data */
                    $data = $this->customFieldUtil->getCustomDataForField($fieldDef, $ticket->getCustomData());
                    $this->addCustomDataProperty($view, $fieldId, $fieldDef, $data, $layoutField->isVisibleOnViewAlways());

                    break;
                case FormFields::ORG_FIELD:
                    $organization = $ticket->getOrganization();
                    if (!$organization instanceof Organization) {
                        break;
                    }
                    /* @var CustomDefOrganization $field_def */
                    if (!$fieldDef = $this->customFieldManager->getCustomOrganizationFieldById($defId)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    if ($fieldDef->isDisplayType()) {
                        // ignore display fields on 'view'
                        break;
                    }

                    /* @var CustomDataOrganization $data */
                    $data = $this->customFieldUtil->getCustomDataForField($fieldDef, $organization->getCustomData());
                    $this->addCustomDataProperty($view, $fieldId, $fieldDef, $data, $layoutField->isVisibleOnViewAlways());

                    break;
                case FormFields::USER_FIELD:
                    /* @var CustomDefPerson $fieldDef */
                    if (!$fieldDef = $this->customFieldManager->getCustomPersonFieldById($defId)) {
                        // ignore fields that don't have a definition. this may rarely happen if admin deletes fields?
                        break;
                    }
                    if ($fieldDef->isDisplayType()) {
                        // ignore display fields on 'view'
                        break;
                    }

                    /* @var CustomDataPerson $data */
                    $data = $this->customFieldUtil->getCustomDataForField($fieldDef, $ticket->person->getCustomData());
                    $this->addCustomDataProperty($view, $fieldId, $fieldDef, $data, $layoutField->isVisibleOnViewAlways());

                    break;
                case FormFields::CUSTOM_FIELD: // per-user custom fields
                    $value = null;

                    /* @var CustomFieldDefinition $fieldDef */
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
                            $fieldId,
                            CustomDefAbstract::TYPE_CHOICE,
                            $fieldDef->getTitle(),
                            $value,
                            $layoutField->isVisibleOnViewAlways()
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

            if (!$def->isFileType()) {
                $value = implode(', ', $value);
            }
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
            is_array($value) ? $value : (string) $value,
            $isAlwaysVisible,
            $def->getOption('clickable_links')
        );

        return $value;
    }

    protected function phrase($phraseName, $vars = [])
    {
        if (is_array($phraseName)) {
            if ($this->isHelpcenter()) {
                $phraseName = array_filter($phraseName, function ($p) {
                    return strpos($p, 'helpcenter.') === 0;
                });
            } else {
                $phraseName = array_filter($phraseName, function ($p) {
                    return strpos($p, 'helpcenter.') !== 0;
                });
            }

            $phraseName = array_pop($phraseName);
        }

        return $this->translate->phrase($phraseName, $vars);
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

    protected function isHelpcenter()
    {
        if ($this->portalBrandThemeLoader) {
            return $this->portalBrandThemeLoader->getPortalBrandTheme($this->brandStack->getActive()->getBrand())->getActiveThemeSet()->getThemeId() === 'helpcenter';
        }

        return false;
    }
}
