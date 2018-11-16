<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver;

use Application\DeskPRO\Entity\Brand;
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
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketPriorityType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketProductType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWorkflowType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketFieldSettings;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
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
     * @var BrandStack
     */
    protected $brandStack;

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
     * @param BrandStack                 $brandStack
     * @param HierarchyGenerator         $hierarchyGenerator
     * @param LanguageManager            $languageManager
     * @param CustomFieldManager         $fieldManager
     * @param TicketFieldSettings        $fieldSettings
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(
        EntityManager              $em,
        BrandStack                 $brandStack,
        HierarchyGenerator         $hierarchyGenerator,
        LanguageManager            $languageManager,
        CustomFieldManager         $fieldManager,
        TicketFieldSettings        $fieldSettings,
        BrandAwareSettingsResolver $settingsResolver
    ) {
        $this->em                 = $em;
        $this->brandStack         = $brandStack;
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
            case FormFields::BRAND:
                return $this->createBrand();
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
    abstract protected function createDepartment(TicketWithLayoutsContext $context);

    /**
     * @return FormField
     */
    abstract protected function createBrand();

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

        if (!$context->isFullLayout()) {
            $person = $this->getSubmittedPerson($context);
            if (!$person instanceof Person) {
                return false;
            }

            // ensure that proper person is set when we are creating user field
            $context->getTicket()->setPerson($person);
        }

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
        if (!$organization && !$context->isFullLayout()) {
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

        $default     = $this->settingsResolver->getSetting('core.default_ticket_cat');
        $isRequired  = $this->fieldSettings->isCategoryRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        // tmp until portal validator annotations are disabled
        if ($this instanceof WebFieldResolver) {
            $constraints[] = new AppAssert\Ticket\TicketLeafCategory();
        }

        return new FormField(TicketCategoryType::class, [
            'label'       => $this->phrase('portal.forms.label_category'),
            'placeholder' => '',
            'required'    => $isRequired,
            'constraints' => $constraints,
            'empty_data'  => $default > 0 ? $default : '',
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

        $default     = $this->settingsResolver->getSetting('core.default_ticket_pri');
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
            'empty_data'  => $default > 0 ? $default : '',
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

        $default     = $this->settingsResolver->getSetting('core.default_ticket_work');
        $isRequired  = $this->fieldSettings->isWorkflowRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        return new FormField(TicketWorkflowType::class, [
            'label'       => $this->phrase('portal.forms.label_workflow'),
            'required'    => $isRequired,
            'constraints' => $constraints,
            'empty_data'  => $default > 0 ? $default : '',
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

        $default     = $this->settingsResolver->getSetting('core.default_prod_id');
        $isRequired  = $this->fieldSettings->isProductRequired($context->isAgentView());
        $constraints = [];
        if ($isRequired) {
            $constraints[] = new Assert\NotBlank();
        }

        // tmp until portal validator annotations are disabled
        if ($this instanceof WebFieldResolver) {
            $constraints[] = new AppAssert\Ticket\TicketLeafProduct();
        }

        return new FormField(TicketProductType::class, [
            'label'       => $this->phrase('portal.forms.label_product'),
            'placeholder' => '',
            'required'    => $isRequired,
            'constraints' => $constraints,
            'empty_data'  => $default > 0 ? $default : '',
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
     * @param Brand                    $brand
     *
     * @return bool
     */
    protected function isNotSelectableDepartment(TicketWithLayoutsContext $context, Brand $brand = null)
    {
        $person    = $context->getOption('person');
        $hierarchy = $this->hierarchyGenerator->generateTicketDepartmentsHierarchy($person, $context->getTicket(), $brand);

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
