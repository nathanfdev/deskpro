<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\TicketLayout\LayoutField;
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
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractFieldResolver.
 */
abstract class AbstractFieldResolver
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var HierarchyGenerator
     */
    protected $hierarchyGenerator;

    /**
     * @var LanguageManager
     */
    protected $languageManager;

    /**
     * @var CustomFieldManager
     */
    protected $fieldManager;

    /**
     * @var TicketFieldSettings
     */
    protected $fieldSettings;

    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager              $em
     * @param HierarchyGenerator         $hierarchyGenerator
     * @param LanguageManager            $languageManager
     * @param CustomFieldManager         $fieldManager
     * @param TicketFieldSettings        $fieldSettings
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(
        EntityManager              $em,
        HierarchyGenerator         $hierarchyGenerator,
        LanguageManager            $languageManager,
        CustomFieldManager         $fieldManager,
        TicketFieldSettings        $fieldSettings,
        BrandAwareSettingsResolver $settingsResolver
    ) {
        $this->em                 = $em;
        $this->hierarchyGenerator = $hierarchyGenerator;
        $this->languageManager    = $languageManager;
        $this->fieldManager       = $fieldManager;
        $this->fieldSettings      = $fieldSettings;
        $this->settingsResolver   = $settingsResolver;
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
                return $this->createSubject($context);
            case FormFields::MESSAGE:
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

        $person = $this->getSubmittedPerson($context);
        if (!$person instanceof Person) {
            return false;
        }

        // ensure that proper person is set when we are creating user field
        $context->getTicket()->setPerson($person);

        return $this->createCustomField($context, 'person.custom_data', $def);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField
     */
    protected function createCustomOrgField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $organization = $this->getSubmittedOrganization($context);
        if (!$organization) {
            return false;
        }

        return $this->createCustomField(
            $context,
            'organization.custom_data',
            $this->fieldManager->getCustomOrganizationFieldById($field->getFieldId())
        );
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     *
     * @return FormField
     */
    protected function createCustomPerField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $def = $this->em->getRepository(CustomFieldDefinition::class)->find($field->getFieldId());
        if (!$def || !$def->isEnabled()) {
            return false;
        }

        if ($def->getContextClass() === Person::class) {
            $owner = $this->getSubmittedPerson($context);
        } elseif ($def->getContextClass() === Organization::class) {
            $owner = $this->getSubmittedOrganization($context);
        } else {
            $owner = null;
        }

        if (!$owner || count($def->getChoices($owner)) < 1) {
            return false;
        }

        return $this->createContextualCustomPerField($context, $def, $owner);
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

        $default = $this->settingsResolver->getSetting('core.default_ticket_cat');
        if ($default) {
            if (!$context->getTicket()->getCategoryId()) {
                $context->getTicket()->setCategoryId($default);
            }
        }

        $isRequired  = $this->fieldSettings->isCategoryRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        return new FormField(TicketCategoryType::class, [
            'label'       => $this->phrase('portal.forms.label_category'),
            'placeholder' => '',
            'required'    => $isRequired,
            'constraints' => $constraints,
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

        $default = $this->settingsResolver->getSetting('core.default_ticket_pri');
        if ($default) {
            if (!$context->getTicket()->getPriorityId()) {
                $context->getTicket()->setPriorityId($default);
            }
        }

        $isRequired  = $this->fieldSettings->isPriorityRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        return new FormField(TicketPriorityType::class, [
            'label'       => $this->phrase('portal.forms.label_priority'),
            'placeholder' => '',
            'required'    => $isRequired,
            'constraints' => $constraints,
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

        $default = $this->settingsResolver->getSetting('core.default_ticket_work');
        if ($default) {
            if (!$context->getTicket()->getWorkflowId()) {
                $context->getTicket()->setWorkflowId($default);
            }
        }

        $isRequired  = $this->fieldSettings->isWorkflowRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        return new FormField(TicketWorkflowType::class, [
            'label'       => $this->phrase('portal.forms.label_workflow'),
            'required'    => $isRequired,
            'constraints' => $constraints,
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

        $default = $this->settingsResolver->getSetting('core.default_prod_id');
        if ($default) {
            if (!$context->getTicket()->getProductId()) {
                $context->getTicket()->setProductId($default);
            }
        }

        $isRequired  = $this->fieldSettings->isProductRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        return new FormField(TicketProductType::class, [
            'label'       => $this->phrase('portal.forms.label_product'),
            'placeholder' => '',
            'required'    => $isRequired,
            'constraints' => $constraints,
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createAttach(TicketWithLayoutsContext $context);

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    abstract protected function createSubject(TicketWithLayoutsContext $context);

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
     * @param string                   $propertyPath
     * @param CustomDefAbstract        $def
     *
     * @return FormField
     */
    abstract protected function createCustomField(TicketWithLayoutsContext $context, $propertyPath, CustomDefAbstract $def = null);

    /**
     * @param TicketWithLayoutsContext $context
     * @param CustomFieldDefinition    $def
     * @param mixed                    $owner
     *
     * @return FormField
     */
    abstract protected function createContextualCustomPerField(TicketWithLayoutsContext $context, CustomFieldDefinition $def, $owner);

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

    /**
     * Get department selectable count to check if it makes sense to render selectbox for department.
     * We can add specific logic to skip this field or render it as hidden.
     *
     * @param TicketWithLayoutsContext $context
     *
     * @return bool
     */
    protected function isNotSelectableDepartment(TicketWithLayoutsContext $context)
    {
        $person    = $context->getOption('person');
        $hierarchy = $this->hierarchyGenerator->generateTicketDepartmentsHierarchy($person);

        return $hierarchy->countSelectable() <= 1;
    }

    /**
     * @param CustomDefAbstract|null   $def
     * @param TicketWithLayoutsContext $context
     *
     * @return bool
     */
    protected function canRenderCustomDef(CustomDefAbstract $def = null, TicketWithLayoutsContext $context)
    {
        return $def
            && $def->isEnabled()
            && $def->getType()
            && (!$def->isAgentField() || ($def->isAgentField() && $context->isAgentView()));
    }

    /**
     * Get actual person ON_SUBMIT event.
     * It can be changed via the form.
     *
     * @param TicketWithLayoutsContext $context
     *
     * @return Person
     */
    abstract protected function getSubmittedPerson(TicketWithLayoutsContext $context);

    /**
     * Get actual ticket organization and compare with submitted person.
     * Person can only see/edit own organization.
     *
     * @param TicketWithLayoutsContext $context
     *
     * @return Organization|bool
     */
    protected function getSubmittedOrganization(TicketWithLayoutsContext $context)
    {
        $person = $this->getSubmittedPerson($context);
        if ($person instanceof Person) {
            $personOrganization = $person->getOrganization();
        } else {
            $personOrganization = null;
        }

        // must be in an organization to see this field
        if (!$personOrganization) {
            return false;
        }

        $ticketOrganization = $context->getTicket()->getOrganization();

        // if ticket has no organization then use person's organization
        if (!$ticketOrganization) {
            $context->getTicket()->setOrganization($personOrganization);
        }

        // person must be a part of the tickets organization to edit org fields
        if ($personOrganization !== $context->getTicket()->getOrganization()) {
            return false;
        }

        return $personOrganization;
    }
}
