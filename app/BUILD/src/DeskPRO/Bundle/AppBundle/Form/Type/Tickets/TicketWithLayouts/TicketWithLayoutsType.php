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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\LabelTicket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldTicketContext;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonEmailType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketCategoryType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDepartmentChoiceType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketPriorityType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketProductType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWorkflowType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Ticket\TicketFieldSettings;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketWithLayoutsType.
 */
class TicketWithLayoutsType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager
     */
    private $field_manager;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory
     */
    private $ticket_layout_factory;

    /**
     * @var HierarchyGenerator
     */
    private $hierarchy_generator;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * @var CustomPerFieldManager
     */
    private $custom_per_field_manager;

    /**
     * @var TicketLayoutHelper
     */
    private $ticket_layout_helper;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TicketFieldSettings
     */
    private $field_settings;

    /**
     * Constructor.
     *
     * @param CustomFieldManager    $field_manager
     * @param TicketLayoutFactory   $ticket_layout_factory
     * @param HierarchyGenerator    $hierarchy_generator
     * @param EntityManager         $em
     * @param LanguageManager       $language_manager
     * @param CustomPerFieldManager $custom_per_field_manager
     * @param TicketLayoutHelper    $ticket_layout_helper
     * @param TicketFieldSettings   $field_settings
     */
    public function __construct(
        CustomFieldManager    $field_manager,
        TicketLayoutFactory   $ticket_layout_factory,
        HierarchyGenerator    $hierarchy_generator,
        EntityManager         $em,
        LanguageManager       $language_manager,
        CustomPerFieldManager $custom_per_field_manager,
        TicketLayoutHelper    $ticket_layout_helper,
        TicketFieldSettings   $field_settings
    ) {
        $this->field_manager            = $field_manager;
        $this->ticket_layout_factory    = $ticket_layout_factory;
        $this->hierarchy_generator      = $hierarchy_generator;
        $this->em                       = $em;
        $this->language_manager         = $language_manager;
        $this->custom_per_field_manager = $custom_per_field_manager;
        $this->ticket_layout_helper     = $ticket_layout_helper;
        $this->field_settings           = $field_settings;
    }

    /**
     * The entire Ticket Form is created using listeners. Nothing exists by default.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onUpdateRelatedData']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'ticket_visibility'   => TicketWithLayoutsContext::VISIBILITY_NEW,
                'ticket_view_context' => TicketWithLayoutsContext::VIEW_USER,
                'data_class'          => Ticket::class,
                'method'              => 'POST',
                'allow_extra_fields'  => false,
                'full_version'        => false,
                'use_captcha'         => true,
                'for_api'             => false,
                'department_id'       => null,
            ])
            ->setRequired([
                'person',
                'settings',
            ])
            ->addAllowedValues([
                'ticket_visibility' => [
                    TicketWithLayoutsContext::VISIBILITY_NEW,
                    TicketWithLayoutsContext::VISIBILITY_EDIT,
                    TicketWithLayoutsContext::VISIBILITY_VIEW,
                ],
            ])
            ->setAllowedTypes([
                'person'        => Person::class,
                'settings'      => SettingsBag::class,
                'department_id' => ['null', 'integer'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_with_layouts';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ticket';
    }

    /**
     * PRE DATA PROCESSING (creates the form based on ticket department).
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $data */
        $data   = $event->getData();
        $form   = $event->getForm();
        $config = $form->getConfig();

        $person = $config->getOption('person');
        $layout = $this->ticket_layout_factory->getLayoutForTicketForm($data->getDepartment() ?: null);

        // Setting ticket person if not defined
        if (!$data->getPerson()) {
            $data->setPerson($config->getOption('person'));
        }

        if ($config->getOption('full_version')) {
            $layout = $this->ticket_layout_factory->getFullLayoutForTicketForm();
        }

        $context = new TicketWithLayoutsContext($form, $data, new TicketLayout());
        $context->setNewLayout($layout);

        // if there is only one department we want to make sure to set it now...
        $hierarchy = $this->hierarchy_generator->generateTicketDepartmentsHierarchy($person);

        // if there is only one dep, and ticket has no dep, just set it on the ticket (we won't be showing the widget)
        if (!$data->getDepartment()) {
            if ($hierarchy->countSelectable() === 1) {
                $data->setDepartment($hierarchy->getFirstSelectable());
            } elseif ($context->getOption('department_id')) {
                $data->setDepartment($this->em->getRepository(Department::class)->find($context->getOption('department_id')));
            }
        }

        $this->manipulateForm($context);
    }

    /**
     * PRE SUBMIT PROCESSING (the data we get here is a pure array of submitted values) (we then manipulate the form if dep changes).
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        /* @var \Application\DeskPRO\Entity\Ticket $ticket */
        $form   = $event->getForm();
        $ticket = $form->getData();
        $data   = $event->getData();

        if ($ticket->getDepartment() && isset($data[FormFields::DEPARTMENT])) {
            if ($ticket->getDepartment()->getId() !== $data[FormFields::DEPARTMENT]) {
                // if department was changed, we need to clear its related data
                $ticket->resetCustomData();
            }
        }

        // calculate the initial layout of the form (before any form submissions took place)
        $layout  = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->getDepartment() ?: null);
        $context = new TicketWithLayoutsContext($form, $ticket, $layout);

        // now we need to compare the department's layout, maybe the layout has changed
        if ($form->has(FormFields::DEPARTMENT) && isset($data[FormFields::DEPARTMENT])) {
            $extracted_data     = $this->ticket_layout_helper->getTicketDataIds($data, $context);
            $new_department_id  = $extracted_data[FormFields::DEPARTMENT];
            $destination_layout = $this->ticket_layout_factory->getLayoutForTicketForm($new_department_id ?: null);

            $context->setNewLayout($destination_layout);
        }

        $this->manipulateForm($context, $data);
    }

    /**
     * @param FormEvent $event
     */
    public function onUpdateRelatedData(FormEvent $event)
    {
        $ticket = $event->getForm()->getData();

        // update ticket message properties
        /** @var TicketMessage $ticket_message */
        $ticket_message = $ticket->messages->first();
        if ($ticket_message) {
            $person = $ticket->getPerson();
            $ticket_message->setPerson($person);
            foreach ($ticket_message->getAttachments() as $attachment) {
                $blob = $attachment->getBlob();
                if ($blob) {
                    $blob->is_temp = false;
                }

                $attachment->setPerson($person);
            }
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param array                    $data
     *
     * @return array of field names that are now displayed on the form
     */
    private function manipulateForm(TicketWithLayoutsContext $context, array $data = [])
    {
        $form                = $context->getForm();
        $extracted_data      = $this->ticket_layout_helper->getTicketDataIds($data, $context);
        $had_previous_layout = count($context->getPreviouslyActiveLayout()->all()) > 0;
        $has_not_submitted   = false;
        $displayed_fields    = isset($data['displayed_fields']) ? array_flip(explode(',', $data['displayed_fields'])) : [];

        $changes = $this->ticket_layout_helper->getLayoutChanges($context, $extracted_data);

        /** @var LayoutField $field */
        foreach ($changes->getAdditionalFields() as $field) {
            if (!$context->hasValidVisibility($field) || $context->getForm()->has($field->getId())) {
                continue;
            }

            if (count($data)) {
                if ($field->hasCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted_data)) {
                    continue;
                }
            } else {
                if (!$context->getOption('full_version') && $field->hasCriteria() && !$field->getCriteria()->isTicketMatch($context->getTicket())) {
                    continue;
                }
            }

            $need_rerender = !$context->forApi() && $had_previous_layout && in_array($field, $changes->getFieldsRequiringRerender());
            $form_field    = $this->createFormField($context, $field, $need_rerender);

            if ($form_field) {
                // we need to collect custom data fields to make custom field groups
                if ($context->forApi() && $form_field->getType() === 'deskpro_custom_data') {
                    $custom_field_groups[$field->getFieldType()][] = [
                        'name'    => $field->getFieldId(),
                        'type'    => $form_field->getType(),
                        'options' => $form_field->getOptions(),
                    ];
                } else {
                    $this->addField($context, $field, $form_field);
                }

                // check if there was submitted data for this field
                if (!array_key_exists($field->getId(), $data) && !isset($displayed_fields[$field->getId()])) {
                    $has_not_submitted = true;
                }
            }
        }

        foreach ($changes->getFieldsToRemove() as $field) {
            $this->removeField($context, $field);
        }

        if ($context->forApi()) {
            // add custom field groups to the form
            $custom_data_mapping = [
                FormFields::TICKET_FIELD => 'fields',
                FormFields::ORG_FIELD    => 'organization_fields',
                FormFields::USER_FIELD   => 'user_fields',
            ];

            foreach ($custom_data_mapping as $field_type => $form_field_name) {
                if (!empty($custom_field_groups[$field_type])) {
                    $form->add($form_field_name, 'deskpro_combined_type', [
                        'forms'          => $custom_field_groups[$field_type],
                        'error_bubbling' => false,
                    ]);
                }
            }
        } else {
            if (!$form->has('displayed_fields')) {
                $form->add('displayed_fields', 'hidden', [
                    'mapped' => false,
                ]);
            }

            // we signal to the controller that we want to rerender (and NOT submit or process) by adding a hidden field
            if ($had_previous_layout && $has_not_submitted && count($changes->getFieldsRequiringRerender()) > 0 && count($data) > 0) {
                if (!$form->has('rerender_form')) {
                    $form->add('rerender_form', 'hidden', [
                        'mapped' => false,
                        'label'  => false,
                    ]);
                }
            }

            // if something is added, we need to ensure "submit" is removed (it's re-added at the end, below)
            if ($form->has('submit')) {
                $form->remove('submit');
            }

            $this->addSubmit($context);
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param FormField                $form_field
     *
     * @return bool
     */
    private function addField(TicketWithLayoutsContext $context, LayoutField $field, FormField $form_field)
    {
        $form = $context->getForm();
        $form->add($field->getId(), $form_field->getType(), $form_field->getOptions());

        // attachments field should have more_attachments button for portal
        if (!$context->forApi() && $field->getFieldType() === FormFields::ATTACHMENTS) {
            $form->add('more_attachments', 'submit', [
                'validation_groups' => false,
                'label'             => $this->phrase('portal.forms.label_add_attachment'),
            ]);
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     */
    private function removeField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $form = $context->getForm();
        if (!$form->has($field->getId())) {
            return;
        }

        $form->remove($field->getId());

        // attachments field should have more_attachments button for portal,
        // so remove it as well
        if ($field->getFieldType() === FormFields::ATTACHMENTS && $form->has('more_attachments')) {
            $form->remove('more_attachments');
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param bool|false               $ignore_validation
     *
     * @return FormField|false
     */
    private function createFormField(TicketWithLayoutsContext $context, LayoutField $field, $ignore_validation = false)
    {
        switch ($field->getFieldType()) {
            case FormFields::SUBJECT:
                return $this->createSubject($ignore_validation);
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
                return $this->createCaptcha($context, $ignore_validation);
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
                return $this->createCustomUserField($context, $field, $ignore_validation);
            case FormFields::ORG_FIELD:
                return $this->createCustomOrgField($context, $field, $ignore_validation);
            case FormFields::TICKET_FIELD:
                return $this->createCustomTicketField($context, $field, $ignore_validation);
            case FormFields::CUSTOM_FIELD:
                return $this->createCustomPerField($context, $field, $ignore_validation);
            default:
                return false;
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createDepartment(TicketWithLayoutsContext $context)
    {
        $person    = $context->getOption('person');
        $hierarchy = $this->hierarchy_generator->generateTicketDepartmentsHierarchy($person);

        // if it is 1 or less to choose from, dont even add this field to the form
        if ($hierarchy->countSelectable() <= 1) {
            return false;
        }

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
     * @param bool $ignore_validation
     *
     * @return FormField
     */
    private function createSubject($ignore_validation)
    {
        $options = [
            'label'       => $this->phrase('portal.forms.label_subject'),
            'required'    => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 5]),
            ],
        ];

        if ($ignore_validation) {
            $options = $this->markNoValidation($options);
        }

        return new FormField('text', $options);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createMessage(TicketWithLayoutsContext $context)
    {
        if (TicketWithLayoutsContext::VISIBILITY_NEW !== $context->getVisibility()) {
            return false;
        }

        return new FormField('ticket_description', [
            'mapped' => false,
            'label'  => false,
            'person' => $context->getPerson(),
            'ticket' => $context->getTicket(),
            'data'   => $context->getMessage(),
            'format' => $context->forApi() ? '' : 'html',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createPerson(TicketWithLayoutsContext $context)
    {
        if ($context->forApi()) {
            return new FormField('deskpro_person_identity', [
                'property_path'    => 'person',
                'person'           => $context->getPerson(),
                'label_name'       => $this->phrase('portal.forms.label_name'),
                'label_email'      => $this->phrase('portal.forms.label_email'),
                'available_fields' => ['id', 'email', 'name'],
                'allow_create'     => true,
            ]);
        } else {
            return new FormField('deskpro_combined_type', [
                'forms' => [
                    $this->createUserNameOptions($context),
                    $this->createUserEmailOptions($context),
                ],
            ]);
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return array
     */
    private function createUserNameOptions(TicketWithLayoutsContext $context)
    {
        return [
            'name'    => FormFields::USER_NAME,
            'type'    => TextType::class,
            'options' => [
                'property_path' => 'person.name',
                'label'         => $this->phrase('portal.forms.label_name'),
                'empty_data'    => $context->getPerson()->getDisplayName(),
            ],
        ];
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return array
     */
    private function createUserEmailOptions(TicketWithLayoutsContext $context)
    {
        $person = $context->getPerson();
        if ($person->isUser()) {
            return [
                'name'    => FormFields::USER_EMAIL,
                'type'    => 'deskpro_person_email_choice',
                'options' => [
                    'property_path' => 'ticket_person_email',
                    'label'         => $this->phrase('portal.forms.label_email'),
                    'person'        => $person,
                ],
            ];
        }

        return [
            'name'    => FormFields::USER_EMAIL,
            'type'    => PersonEmailType::class,
            'options' => [
                'property_path' => 'person.primary_email',
                'label'         => false,
                'constraints'   => [], // ignore the "unique entity" constraint here
            ],
        ];
    }

    /**
     * @return FormField
     */
    private function createUserTimezone()
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
    private function createLabelsField(TicketWithLayoutsContext $context)
    {
        return new FormField('api_labels_collection', [
            'labels_class'   => LabelTicket::class,
            'labels_owner'   => $context->getTicket(),
            'owner_property' => 'ticket',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param bool|false               $ignore_validation
     *
     * @return FormField
     */
    private function createCustomTicketField(TicketWithLayoutsContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomTicketFieldById($field->getFieldId());

        return $this->createCustomField($context, 'custom_data', $field_def, $ignore_validation);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param bool|false               $ignore_validation
     *
     * @return FormField
     */
    private function createCustomUserField(TicketWithLayoutsContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomPersonFieldById($field->getFieldId());

        return $this->createCustomField($context, 'person.custom_data', $field_def, $ignore_validation);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param bool|false               $ignore_validation
     *
     * @return FormField
     */
    private function createCustomOrgField(TicketWithLayoutsContext $context, LayoutField $field, $ignore_validation = false)
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

        $field_def = $this->field_manager->getCustomOrganizationFieldById($field->getFieldId());

        return $this->createCustomField($context, 'organization.custom_data', $field_def, $ignore_validation);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param string                   $property_path
     * @param CustomDefAbstract        $field_def
     * @param bool                     $ignore_validation
     *
     * @return FormField
     */
    private function createCustomField(TicketWithLayoutsContext $context, $property_path, CustomDefAbstract $field_def = null, $ignore_validation = false)
    {
        if (!$field_def || !$field_def->isEnabled()) {
            return false;
        }

        $options = [
            'custom_def'      => $field_def,
            'property_path'   => $property_path,
            'agent_interface' => $context->getViewContext() === TicketWithLayoutsContext::VIEW_AGENT,
            'label'           => $field_def->getTitle(),
            'required'        => $field_def->isRequired(),
            'inline'          => $context->forApi(),
        ];

        if (in_array($field_def->getHandlerClass(), [
            'Application\DeskPRO\CustomFields\Handler\Hidden',
            'Application\DeskPRO\CustomFields\Handler\Display',
        ])) {
            $options['label'] = false;
        }

        if ($ignore_validation) {
            $options                      = $this->markNoValidation($options);
            $options['ignore_validation'] = true;
        }

        return new FormField('deskpro_custom_data', $options);
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param bool|false               $ignore_validation
     *
     * @return FormField
     */
    private function createCustomPerField(TicketWithLayoutsContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_context = new CustomFieldTicketContext($context->getTicket());
        $def           = $this->custom_per_field_manager->getCustomPerFieldDefinition($field->getFieldId(), $field_context);

        if (!$def || !$def->isEnabled()) {
            return false;
        }

        $possible_choices = $this->custom_per_field_manager->getCustomPerFieldChoices($def, $field_context);
        if (count($possible_choices) < 1) {
            return false;
        }

        $data    = $this->custom_per_field_manager->getOrCreateCustomPerFieldData($def, $field_context);
        $options = [
            'agent_interface'             => $context->getViewContext() === TicketWithLayoutsContext::VIEW_AGENT,
            'label'                       => $def->getTitle(),
            'data'                        => $data,
            'custom_per_field_context'    => $field_context,
            'custom_per_field_definition' => $def,
            'mapped'                      => false,
        ];

        if ($ignore_validation) {
            $options = $this->markNoValidation($options);
        }

        return new FormField('deskpro_custom_per_field_data', $options);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createCategory(TicketWithLayoutsContext $context)
    {
        if (!$this->field_settings->canCategoryBeDisplayed()) {
            return false;
        }

        $default = $context->getSetting('core.default_ticket_cat', null);
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
    private function createPriority(TicketWithLayoutsContext $context)
    {
        if (!$this->field_settings->canPriorityBeDisplayed()) {
            return false;
        }

        $default = $context->getSetting('core.default_ticket_pri', null);
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
    private function createWorkflow(TicketWithLayoutsContext $context)
    {
        if (!$this->field_settings->canWorkflowBeDisplayed()) {
            return false;
        }

        $default = $context->getSetting('core.default_ticket_work', null);
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
    private function createProduct(TicketWithLayoutsContext $context)
    {
        if (!$this->field_settings->canProductBeDisplayed()) {
            return false;
        }

        $default = $context->getSetting('core.default_prod_id', null);
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
     * @param bool                     $ignore_validation
     *
     * @return FormField
     */
    private function createCaptcha(TicketWithLayoutsContext $context, $ignore_validation)
    {
        if (!$context->getOption('use_captcha') || $context->forApi()) {
            return false;
        }

        // NOTE: you may want to view TicketLayoutFactory.
        // In TicketLayoutFactory we can, at times, add a CAPTCHA to the ticket
        // layout under certain circumstances (when anti-abuse is violated, for example).

        $options = [
            'mapped'         => false,
            'error_bubbling' => false,
        ];

        if ($ignore_validation) {
            $options = $this->markNoValidation($options);
        }

        return new FormField('deskpro_captcha', $options);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createCc(TicketWithLayoutsContext $context)
    {
        return new FormField('ticket_participants', [
            'label'         => $this->phrase('portal.forms.label_cc'),
            'owner'         => $context->getTicket(),
            'is_agent'      => false,
            'property_path' => 'participants',
            'required'      => false,
            'view_type'     => $context->forApi() ? 'array' : 'inline',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createFollowers(TicketWithLayoutsContext $context)
    {
        return new FormField('ticket_participants', [
            'label'         => $this->phrase('portal.forms.label_followers'),
            'owner'         => $context->getTicket(),
            'is_agent'      => true,
            'property_path' => 'participants',
            'required'      => false,
            'view_type'     => $context->forApi() ? 'array' : 'inline',
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return FormField
     */
    private function createAttach(TicketWithLayoutsContext $context)
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
     */
    private function addSubmit(TicketWithLayoutsContext $context)
    {
        if ($context->getVisibility() !== TicketWithLayoutsContext::VISIBILITY_NEW) {
            $label = $this->phrase('portal.forms.label_save');
        } else {
            $label = $this->phrase('portal.forms.label_submit');
        }

        $context->getForm()->add('submit', 'submit', [
            'label' => $label,
        ]);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function markNoValidation(array $options)
    {
        return array_merge($options, [
            'validation_groups' => [],
            'constraints'       => [],
        ]);
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return string
     */
    private function phrase($name, array $vars = [])
    {
        return $this->language_manager->phrase($name, $vars);
    }
}
