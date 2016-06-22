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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldTicketContext;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketCategoryType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDepartmentChoiceType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketPriorityType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketProductType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWorkflowType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketFieldSettings;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractFieldResolver.
 */
abstract class AbstractFieldResolver
{
    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var CustomFieldManager
     */
    private $fieldManager;

    /**
     * @var CustomPerFieldManager
     */
    private $customPerFieldManager;

    /**
     * @var TicketFieldSettings
     */
    private $fieldSettings;

    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator         $hierarchyGenerator
     * @param LanguageManager            $languageManager
     * @param CustomFieldManager         $fieldManager
     * @param CustomPerFieldManager      $customPerFieldManager
     * @param TicketFieldSettings        $fieldSettings
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(
        HierarchyGenerator         $hierarchyGenerator,
        LanguageManager            $languageManager,
        CustomFieldManager         $fieldManager,
        CustomPerFieldManager      $customPerFieldManager,
        TicketFieldSettings        $fieldSettings,
        BrandAwareSettingsResolver $settingsResolver
    ) {
        $this->hierarchyGenerator    = $hierarchyGenerator;
        $this->languageManager       = $languageManager;
        $this->fieldManager          = $fieldManager;
        $this->customPerFieldManager = $customPerFieldManager;
        $this->fieldSettings         = $fieldSettings;
        $this->settingsResolver      = $settingsResolver;
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField|false
     */
    public function createFormField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        switch ($field->getFieldType()) {
            case FormFields::SUBJECT:
                return $this->createSubject();
            case FormFields::MESSAGE:
                if (TicketWithLayoutsContext::VISIBILITY_NEW !== $context->getVisibility()) {
                    return false;
                }

                return $this->createMessage($context);
            case FormFields::PERSON:
                return $this->createPerson($context);
            case FormFields::DEPARTMENT:
                return $this->createDepartment($context);
            case FormFields::CATEGORY:
                return $this->createCategory($context);
            case FormFields::PRIORITY:
                return $this->createPriority($context);
            case FormFields::WORKFLOW:
                return $this->createWorkflow($context);
            case FormFields::PRODUCT:
                return $this->createProduct($context);
            case FormFields::CAPTCHA:
                return $this->createCaptcha($context);
            case FormFields::CC:
                return $this->createCc($context);
            case FormFields::FOLLOWERS:
                return $this->createFollowers($context);
            case FormFields::ATTACHMENTS:
                return $this->createAttach($context);
            case FormFields::USER_TIMEZONE:
                return $this->createUserTimezone();
            case FormFields::LABELS:
                return $this->createLabelsField($context);
            case FormFields::USER_FIELD:
                return $this->createCustomUserField($context, $field);
            case FormFields::ORG_FIELD:
                return $this->createCustomOrgField($context, $field);
            case FormFields::TICKET_FIELD:
                return $this->createCustomTicketField($context, $field);
            case FormFields::CUSTOM_FIELD:
                return $this->createCustomPerField($context, $field);
            default:
                return false;
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createDepartment(TicketWithLayoutsContext $context)
    {
        return new FormField(TicketDepartmentChoiceType::class, [
            'label'       => $this->phrase('portal.forms.label_department'),
            'person'      => $context->getPerson(),
            'ticket'      => $context->getTicket(),
            'placeholder' => '',
            'constraints' => [
                new Assert\NotNull(),
            ],
        ]);
    }

    /**
     * @return FormField
     */
    protected function createSubject()
    {
        $options = [
            'label'       => $this->phrase('portal.forms.label_subject'),
            'required'    => true,
            'constraints' => [
                new Assert\Length(['min' => 5]),
            ],
        ];

        return new FormField('text', $options);
    }

    /**
     * @return FormField
     */
    protected function createUserTimezone()
    {
        return new FormField('timezone', [
            'property_path' => 'person.timezone',
            'label'         => $this->phrase('portal.forms.label_timezone'),
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createLabelsField(TicketWithLayoutsContext $context)
    {
        // Users can never set labels, so guard against that
        if ($context->getViewContext() === TicketWithLayoutsContext::VIEW_USER) {
            return false;
        }

        return new FormField(LabelsCollectionType::class, [
            'labels_class'   => LabelTicket::class,
            'labels_owner'   => $context->getTicket(),
            'owner_property' => 'ticket',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField
     */
    protected function createCustomTicketField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $field_def = $this->fieldManager->getCustomTicketFieldById($field->getFieldId());

        return $this->createCustomField($context, 'custom_data', $field_def);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField
     */
    protected function createCustomUserField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $def = $this->fieldManager->getCustomPersonFieldById($field->getFieldId());

        $field = $this->createCustomField($context, 'person.custom_data', $def);
        $field->setOption('owner_form', $context->getForm()->get(FormFields::PERSON));

        return $field;
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField
     */
    protected function createCustomOrgField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $person_organization = $context->getPerson()->getOrganization();
        $ticket_organization = $context->getTicket()->getOrganization();

        if (!$ticket_organization) {
            // must be in an organization to see this field
            if (!$person_organization) {
                return false;
            }

            $ticket_organization = $person_organization;
        }

        // person must be a part of the tickets organization to edit org fields
        if ($person_organization !== $ticket_organization) {
            return false;
        }

        $field_def = $this->fieldManager->getCustomOrganizationFieldById($field->getFieldId());

        return $this->createCustomField($context, 'organization.custom_data', $field_def);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField
     */
    protected function createCustomPerField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $field_context = new CustomFieldTicketContext($context->getTicket());
        $def           = $this->customPerFieldManager->getCustomPerFieldDefinition($field->getFieldId(), $field_context);

        if (!$def || !$def->isEnabled()) {
            return false;
        }

        $possible_choices = $this->customPerFieldManager->getCustomPerFieldChoices($def, $field_context);
        if (count($possible_choices) < 1) {
            return false;
        }

        $data    = $this->customPerFieldManager->getOrCreateCustomPerFieldData($def, $field_context);
        $options = [
            'agent_interface'             => $context->getViewContext() === TicketWithLayoutsContext::VIEW_AGENT,
            'label'                       => $def->getTitle(),
            'data'                        => $data,
            'custom_per_field_context'    => $field_context,
            'custom_per_field_definition' => $def,
            'mapped'                      => false,
        ];

        return new FormField('deskpro_custom_per_field_data', $options);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createCategory(TicketWithLayoutsContext $context)
    {
        if (!$this->fieldSettings->canCategoryBeDisplayed()) {
            return false;
        }

        $default = $this->settingsResolver->getSetting('core.default_ticket_cat', null);
        if ($default) {
            if (!$context->getTicket()->getCategoryId()) {
                $context->getTicket()->setCategoryId($default);
            }
        }

        return new FormField(TicketCategoryType::class, [
            'label'       => $this->phrase('portal.forms.label_category'),
            'placeholder' => '',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createPriority(TicketWithLayoutsContext $context)
    {
        if (!$this->fieldSettings->canPriorityBeDisplayed()) {
            return false;
        }

        $default = $this->settingsResolver->getSetting('core.default_ticket_pri', null);
        if ($default) {
            if (!$context->getTicket()->getPriorityId()) {
                $context->getTicket()->setPriorityId($default);
            }
        }

        return new FormField(TicketPriorityType::class, [
            'label'       => $this->phrase('portal.forms.label_priority'),
            'placeholder' => '',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createWorkflow(TicketWithLayoutsContext $context)
    {
        if (!$this->fieldSettings->canWorkflowBeDisplayed()) {
            return false;
        }

        if (!$context->isAgentView()) {
            return false;
        }

        $default = $this->settingsResolver->getSetting('core.default_ticket_work', null);
        if ($default) {
            if (!$context->getTicket()->getWorkflowId()) {
                $context->getTicket()->setWorkflowId($default);
            }
        }

        return new FormField(TicketWorkflowType::class, [
            'label' => $this->phrase('portal.forms.label_workflow'),
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createProduct(TicketWithLayoutsContext $context)
    {
        if (!$this->fieldSettings->canProductBeDisplayed()) {
            return false;
        }

        $default = $this->settingsResolver->getSetting('core.default_prod_id', null);
        if ($default) {
            if (!$context->getTicket()->getProductId()) {
                $context->getTicket()->setProductId($default);
            }
        }

        return new FormField(TicketProductType::class, [
            'placeholder' => '',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    protected function createAttach(TicketWithLayoutsContext $context)
    {
        if (!$context->getMessage()) {
            return false;
        }

        return new FormField('ticket_message_attachment_collection', [
            'property_path'  => 'messages[0].attachments',
            'required'       => false,
            'person'         => $context->getPerson(),
            'ticket_message' => $context->getMessage(),
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createMessage(TicketWithLayoutsContext $context);

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createPerson(TicketWithLayoutsContext $context);

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createCaptcha(TicketWithLayoutsContext $context);

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createCc(TicketWithLayoutsContext $context);

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createFollowers(TicketWithLayoutsContext $context);

    /**
     * @param TicketWithLayoutsContext $context
     * @param string                   $propertyPath
     * @param CustomDefAbstract        $def
     *
     * @return FormField
     */
    abstract protected function createCustomField(TicketWithLayoutsContext $context, $propertyPath, CustomDefAbstract $def = null);

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    protected function phrase($name, array $vars = [])
    {
        return $this->languageManager->phrase($name, $vars);
    }
}
