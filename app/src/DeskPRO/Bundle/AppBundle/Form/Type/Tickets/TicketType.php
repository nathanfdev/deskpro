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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldTicketContext;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Form\DefaultValueForTicketLayoutField;
use DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager;
use DeskPRO\Bundle\AppBundle\Form\Form\TicketFormContext;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutDiffer;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket\LeafDepartment;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Class TicketType.
 */
class TicketType extends AbstractType
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Form\Form\FormFieldManager
     */
    private $field_manager;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory
     */
    private $ticket_layout_factory;

    /**
     * @var \DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutDiffer
     */
    private $layout_differ;

    /**
     * @var EntityManager
     */
    private $em;

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
     * @var DefaultValueForTicketLayoutField
     */
    private $default_value_finder;

    /**
     * Constructor.
     *
     * @param FormFieldManager                 $field_manager
     * @param TicketLayoutFactory              $ticket_layout_factory
     * @param TicketLayoutDiffer               $layout_differ
     * @param HierarchyGenerator               $hierarchy_generator
     * @param EntityManager                    $em
     * @param LanguageManager                  $language_manager
     * @param CustomPerFieldManager            $custom_per_field_manager
     * @param DefaultValueForTicketLayoutField $default_value_finder
     */
    public function __construct(
        FormFieldManager                 $field_manager,
        TicketLayoutFactory              $ticket_layout_factory,
        TicketLayoutDiffer               $layout_differ,
        HierarchyGenerator               $hierarchy_generator,
        EntityManager                    $em,
        LanguageManager                  $language_manager,
        CustomPerFieldManager            $custom_per_field_manager,
        DefaultValueForTicketLayoutField $default_value_finder
    ) {
        $this->layout_differ            = $layout_differ;
        $this->field_manager            = $field_manager;
        $this->ticket_layout_factory    = $ticket_layout_factory;
        $this->hierarchy_generator      = $hierarchy_generator;
        $this->em                       = $em;
        $this->language_manager         = $language_manager;
        $this->custom_per_field_manager = $custom_per_field_manager;
        $this->default_value_finder     = $default_value_finder;
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
    }

    /**
     * PRE DATA PROCESSING (creates the form based on ticket department).
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $event->getData();
        $form   = $event->getForm();
        $config = $form->getConfig();
        $person = $config->getOption('person');
        $layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->getDepartment() ?: null);

        // Setting ticket person if not defined
        if (!$ticket->getPerson()) {
            $ticket->setPerson($config->getOption('person'));
        }

        if ($config->getOption('full_version')) {
            $layout = $this->ticket_layout_factory->getFullLayoutForTicketForm();
        }

        $context = $this->createTicketFormContext($form, $ticket, $layout);

        // if there is only one department we want to make sure to set it now...
        $hierarchy = $this->hierarchy_generator->generateTicketDepartmentsHierarchy($person);

        // if there is only one dep, and ticket has no dep, just set it on the ticket (we won't be showing the widget)
        if (!$ticket->getDepartment() && $hierarchy->countSelectable() == 1) {
            $ticket->setDepartment($hierarchy->getFirstSelectable());
        }

        $displaying_fields = $this->manipulateForm(new Layout(), $context->getActiveLayout(), $context);

        if ($form->has('displayed_fields')) {
            $form->remove('displayed_fields');
        }

        $form->add('displayed_fields', 'hidden', [
            'mapped' => false,
            'data'   => $displaying_fields['displayed_fields'],
        ]);
    }

    /**
     * PRE SUBMIT PROCESSING (the data we get here is a pure array of submitted values) (we then manipulate the form if dep changes).
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        /* @var \Application\DeskPRO\Entity\Ticket $ticket */
        $form                     = $event->getForm();
        $ticket                   = $form->getData();
        $pre_submit_data          = $event->getData();
        $already_displayed_fields = [];

        if (array_key_exists('displayed_fields', $pre_submit_data)) {
            $already_displayed_fields = explode(',', $pre_submit_data['displayed_fields']);
        }

        // calculate the initial layout of the form (before any form submissions took place)
        $layout         = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->getDepartment() ?: null);
        $context        = $this->createTicketFormContext($form, $ticket, $layout, $already_displayed_fields);
        $initial_layout = $context->getActiveLayout();

        // now we need to compare the department's layout, maybe the layout has changed
        if ($form->has(FormFields::DEPARTMENT) && isset($pre_submit_data[FormFields::DEPARTMENT])) {
            $extracted_data    = $this->getTicketDataIds($pre_submit_data, $context);
            $new_department_id = $extracted_data['department'];

            if ($form->has('last_department_id')) {
                $form->remove('last_department_id');
            }

            $form->add('last_department_id', 'hidden', [
                'mapped' => false,
                'label'  => false,
            ]);

            $event->setData(array_merge($pre_submit_data, [
                'last_department_id' => $new_department_id,
            ]));

            $destination_layout = $this->ticket_layout_factory->getLayoutForTicketForm($new_department_id ?: null);
            $context->setNewLayout($destination_layout);
        }

        // when manipulating the form, it may return data that we need to add to the pre submit event's data (new defaults)
        $extra_data_to_submit = $this->manipulateForm(
            $initial_layout,
            $context->getActiveLayout(),
            $context,
            $pre_submit_data
        );

        $event->setData(array_merge($event->getData(), $extra_data_to_submit));
    }

    /**
     * @param Layout            $initial_layout
     * @param Layout            $new_layout
     * @param TicketFormContext $context
     * @param array             $submitted_data
     *
     * @return array of field names that are now displayed on the form
     */
    private function manipulateForm(Layout $initial_layout, Layout $new_layout, TicketFormContext $context, $submitted_data = [])
    {
        $additional_fields             = $this->layout_differ->findFieldsToAdd($initial_layout, $new_layout);
        $fields_to_remove              = $this->layout_differ->findFieldsToRemove($initial_layout, $new_layout);
        $extracted_data                = $this->getTicketDataIds($submitted_data, $context);
        $pre_existing_displayed_fields = $context->getPreviouslyDisplayedFields();
        $had_previous_layout           = count($initial_layout->all()) > 0;

        list($fields_requiring_rerender, $fields_to_remove, $additional_fields) = $this->useLayoutCriteriaToDetermineDynamicLayoutChanges($new_layout, $context, $extracted_data, $fields_to_remove, $additional_fields);

        $form = $context->getForm();

        $added_something       = false;
        $new_fields_to_display = [];
        $extra_data_to_submit  = [];
        foreach ($additional_fields as $field) {
            if (!$context->hasValidVisibility($field)) {
                continue;
            }

            if (count($submitted_data)) {
                if ($field->hasCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted_data)) {
                    continue;
                }
            } else {
                if (!$form->getConfig()->getOption('full_version') && $field->hasCriteria() && !$field->getCriteria()->isTicketMatch($context->getTicket())) {
                    continue;
                }
            }

            // if something is added, we need to ensure "submit" is removed (it's re-added at the end, below)
            if ($context->getForm()->has('submit')) {
                $context->getForm()->remove('submit');
            }

            // we signal to the controller that we want to rerender (and NOT submit or process) by adding a hidden field
            if (count($fields_requiring_rerender) > 0 && count($submitted_data) > 0 && !$context->getForm()->has('rerender_form')) {
                $context->getForm()->add('rerender_form', 'hidden', [
                    'mapped' => false,
                    'label'  => false,
                ]);
            }

            $added_something = true;

            $new_fields_to_display[] = $field->getId();
            $field_requires_rerender = in_array($field, $fields_requiring_rerender) && $had_previous_layout;

            // attach the default value to the submitted values of the form (to newly added fields that need a re-render)
            if (count($submitted_data) && $field_requires_rerender) {
                $ticket_field_id = $field->getId();
                $default_value   = $this->default_value_finder->determineDefaultSubmitData($field);

                if ($default_value) {
                    $extra_data_to_submit[$ticket_field_id] = $default_value;
                }
            }

            $this->addField($context, $field, $field_requires_rerender);
        }

        foreach ($fields_to_remove as $field) {
            $this->removeField($context, $field);
            if (($key = array_search($field->getId(), $pre_existing_displayed_fields)) !== false) {
                unset($pre_existing_displayed_fields[$key]);
            }
        }

        if (!$added_something && $context->getForm()->has('rerender_form')) {
            // we didn't add anything new, so remove the signal to re-render
            $context->getForm()->remove('rerender_form');
        }

        $this->addSubmit($context);

        $displayed_fields_data = [
            'displayed_fields' => implode(',', array_merge($pre_existing_displayed_fields, $new_fields_to_display)),
        ];

        return array_merge($extra_data_to_submit, $displayed_fields_data);
    }

    /**
     * Submitted choice values are not submitted with the entity Id. Instead we are given the choice list key.
     *
     * This inspects the submitted data on our form and gives us data we're interesed in.
     *
     * @param array             $submitted_data
     * @param TicketFormContext $context
     *
     * @return array the form key and its selected entity ID (or null if not submitted)
     */
    private function getTicketDataIds(array $submitted_data, TicketFormContext $context)
    {
        $form       = $context->getForm();
        $final_data = [];
        $keys       = [
            FormFields::DEPARTMENT,
            FormFields::PRODUCT,
            FormFields::CATEGORY,
            FormFields::WORKFLOW,
            FormFields::PRIORITY,
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $submitted_data) && $form->has($key)) {
                $submitted_value = $submitted_data[$key];
                $choice          = current($form->get($key)->getConfig()->getOption('choice_list')->getChoicesForValues([$submitted_value]));

                if ($choice instanceof HierarchyNode) {
                    $choice = $choice->getData();
                }

                if ($choice) {
                    $final_data[$key] = $choice->getId();
                } else {
                    $final_data[$key] = null;
                }
            } else {
                $final_data[$key] = null;
            }
        }

        return $final_data;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'ticket_visibility'   => TicketFormContext::VISIBILITY_NEW,
                'ticket_view_context' => TicketFormContext::VIEW_USER,
                'data_class'          => 'Application\\DeskPRO\\Entity\\Ticket',
                'method'              => 'POST',
                'allow_extra_fields'  => true,
                'full_version'        => false,
                'use_captcha'         => true,
            ])
            ->setRequired([
                'person',
                'settings',
            ])
            ->addAllowedValues([
                'ticket_visibility' => [
                    TicketFormContext::VISIBILITY_NEW,
                    TicketFormContext::VISIBILITY_EDIT,
                    TicketFormContext::VISIBILITY_VIEW,
                ],
            ])
            ->setAllowedTypes([
                'person'   => 'Application\\DeskPRO\\Entity\\Person',
                'settings' => 'Application\\DeskPRO\\NewSettings\\SettingsBag',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket';
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function removeField(TicketFormContext $context, LayoutField $field)
    {
        if (!$context->getForm()->has($field->getId())) {
            return;
        }

        $context->getForm()->remove($field->getId());
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     * @param bool|false        $ignore_validation
     */
    private function addField(TicketFormContext $context, LayoutField $field, $ignore_validation = false)
    {
        if ($context->getForm()->has($field->getId())) {
            return;
        }

        if ($this->shouldFieldBeSkipped($field, $context)) {
            return;
        }

        $field_type = $field->getFieldType();

        switch ($field_type) {
            case FormFields::SUBJECT:
                $this->addSubject($context, $ignore_validation);
                break;
            case FormFields::MESSAGE:
                $this->addMessage($context, $field);
                break;
            case FormFields::USER_NAME_AND_EMAIL:
                $this->addUserNameAndEmail($context, $field);
                break;
            case FormFields::DEPARTMENT:
                $this->addDepartment($context, $field);
                break;
            case FormFields::CATEGORY:
                $this->addCategory($context, $field);
                break;
            case FormFields::PRIORITY:
                $this->addPriority($context, $field);
                break;
            case FormFields::WORKFLOW:
                $this->addWorkflow($context, $field);
                break;
            case FormFields::PRODUCT:
                $this->addProduct($context, $field);
                break;
            case FormFields::CAPTCHA:
                $this->addCaptcha($context, $field, $ignore_validation);
                break;
            case FormFields::CC:
                $this->addCc($context, $field);
                break;
            case FormFields::ATTACH:
                $this->addAttach($context);
                break;
            case FormFields::USER_EMAIL:
                $this->addUserEmail($context);
                break;
            case FormFields::USER_NAME:
                $this->addUserName($context);
                break;
            case FormFields::USER_TIMEZONE:
                $this->addUserTimezone($context, $field);
                break;
            case FormFields::USER_FIELD:
                $this->addCustomUserField($context, $field, $ignore_validation);
                break;
            case FormFields::ORG_FIELD:
                $this->addCustomOrgField($context, $field, $ignore_validation);
                break;
            case FormFields::TICKET_FIELD:
                $this->addCustomTicketField($context, $field, $ignore_validation);
                break;
            case FormFields::CUSTOM_FIELD:
                $this->addCustomPerField($context, $field, $ignore_validation);
                break;
        }
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addDepartment(TicketFormContext $context, LayoutField $field)
    {
        $person    = $context->getOption('person');
        $hierarchy = $this->hierarchy_generator->generateTicketDepartmentsHierarchy($person);

        // if it is 1 or less to choose from, dont even add this field to the form
        if ($hierarchy->countSelectable() <= 1) {
            return;
        }

        $context->getForm()->add($field->getId(), 'deskpro_department', [
            'label'       => $this->phrase('portal.forms.label_department'),
            'person'      => $context->getPerson(),
            'ticket'      => $context->getTicket(),
            'placeholder' => '',
            'constraints' => [
                new LeafDepartment(['message' => 'portal.forms.error_ticket_department_invalid']),
                new NotNull(['message' => 'portal.forms.error_ticket_department_required']),
            ],
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param bool|false        $ignore_validation
     */
    private function addSubject(TicketFormContext $context, $ignore_validation = false)
    {
        $options = [
            'label'       => $this->phrase('portal.forms.label_subject'),
            'required'    => true,
            'constraints' => [
                new NotBlank([
                    'message' => 'portal.forms.error_ticket_subject_required',
                ]),
                new Length([
                    'min'        => 5,
                    'minMessage' => 'portal.forms.error_ticket_subject_length',
                ]),
            ],
        ];

        if ($ignore_validation) {
            $options = $this->markNoValidation($options);
        }

        $context->getForm()->add('subject', 'text', $options);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addMessage(TicketFormContext $context, LayoutField $field)
    {
        if (TicketFormContext::VISIBILITY_NEW !== $context->getVisibility()) {
            return;
        }

        $context->getForm()->add($field->getId(), 'ticket_description', [
            'mapped' => false,
            'label'  => false,
            'person' => $context->getPerson(),
            'ticket' => $context->getTicket(),
            'data'   => $context->getMessage(),
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addUserNameAndEmail(TicketFormContext $context, LayoutField $field)
    {
        $context->getForm()->add(
            $field->getId(),
            'deskpro_combined_type',
            [
                'forms' => [
                    $this->createUserName($context),
                    $this->createUserEmail($context),
                ],
            ]
        );
    }

    /**
     * @param TicketFormContext $context
     */
    private function addUserName(TicketFormContext $context)
    {
        $form = $this->createUserName($context);
        $context->getForm()->add(
            $form['name'],
            $form['type'],
            $form['options']
        );
    }

    /**
     * @param TicketFormContext $context
     *
     * @return array
     */
    private function createUserName(TicketFormContext $context)
    {
        return [
            'name'    => FormFields::USER_NAME,
            'type'    => 'text',
            'options' => [
                'property_path' => 'person.name',
                'label'         => $this->phrase('portal.forms.label_name'),
                'empty_data'    => $context->getPerson()->getDisplayName(),
            ],
        ];
    }

    /**
     * @param TicketFormContext $context
     */
    private function addUserEmail(TicketFormContext $context)
    {
        $form = $this->createUserEmail($context);
        $context->getForm()->add(
            $form['name'],
            $form['type'],
            $form['options']
        );
    }

    /**
     * @param TicketFormContext $context
     *
     * @return array
     */
    private function createUserEmail(TicketFormContext $context)
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
        } else {
            return [
                'name'    => FormFields::USER_EMAIL,
                'type'    => 'deskpro_person_email',
                'options' => [
                    'property_path' => 'person.primary_email',
                    'label'         => false,
                    'constraints'   => [], // ignore the "unqiue entity" constraint here
                ],
            ];
        }
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addUserTimezone(TicketFormContext $context, LayoutField $field)
    {
        $context->getForm()->add($field->getId(), 'timezone', [
            'property_path' => 'person.timezone',
            'label'         => $this->phrase('portal.forms.label_timezone'),
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     * @param bool|false        $ignore_validation
     *
     * @return bool
     */
    private function addCustomTicketField(TicketFormContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomTicketFieldById($field->getFieldId());
        if (!$field_def || !$field_def->isEnabled()) {
            return;
        }

        $options = [
            'custom_data_field' => $field_def,
            'ticket'            => $context->getTicket(),
            'property_path'     => sprintf('getCustomDataCollection[%s]', $field->getFieldId()),
            'agent_interface'   => $context->getViewContext() === TicketFormContext::VIEW_AGENT,
            'label'             => $field_def->getTitle(),
            'required'          => $field_def->isRequired(),
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

        $context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_ticket',
            $options
        );
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     * @param bool|false        $ignore_validation
     */
    private function addCustomUserField(TicketFormContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomPersonFieldById($field->getFieldId());
        if (!$field_def->isEnabled()) {
            return;
        }

        $options = [
            'custom_data_field' => $field_def,
            'person'            => $context->getPerson(),
            'property_path'     => sprintf('person.getCustomDataCollection[%s]', $field->getFieldId()),
            'agent_interface'   => $context->getViewContext() === TicketFormContext::VIEW_AGENT,
            'label'             => $field_def->getTitle(),
        ];

        if ($ignore_validation) {
            $options                      = $this->markNoValidation($options);
            $options['ignore_validation'] = true;
        }

        $context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_person',
            $options
        );
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     * @param bool|false        $ignore_validation
     */
    private function addCustomOrgField(TicketFormContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomOrganizationFieldById($field->getFieldId());
        if (!$field_def->isEnabled()) {
            return;
        }

        $ticket_organization = $context->getTicket()->getOrganization();
        if (!$ticket_organization) {
            // must be in an organization to see this field
            return;
        }
        if ($context->getPerson()->getOrganization() !== $ticket_organization) {
            // person must be a part of the tickets organization to edit org fields
            return;
        }

        $options = [
            'custom_data_field' => $field_def,
            'organization'      => $ticket_organization,
            'property_path'     => sprintf('organization.getCustomDataCollection[%s]', $field->getFieldId()),
            'agent_interface'   => $context->getViewContext() === TicketFormContext::VIEW_AGENT,
            'label'             => $field_def->getTitle(),
        ];

        if ($ignore_validation) {
            $options                      = $this->markNoValidation($options);
            $options['ignore_validation'] = true;
        }

        $context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_organization',
            $options
        );
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     * @param bool|false        $ignore_validation
     */
    private function addCustomPerField(TicketFormContext $context, LayoutField $field, $ignore_validation = false)
    {
        $field_context = new CustomFieldTicketContext($context->getTicket());
        $def           = $this->custom_per_field_manager->getCustomPerFieldDefinition($field->getFieldId(), $field_context);

        if (!$def || !$def->isEnabled()) {
            return;
        }

        $possible_choices = $this->custom_per_field_manager->getCustomPerFieldChoices($def, $field_context);
        if (count($possible_choices) < 1) {
            return;
        }

        $data = $this->custom_per_field_manager->getOrCreateCustomPerFieldData($def, $field_context);

        $options = [
            'agent_interface'             => $context->getViewContext() === TicketFormContext::VIEW_AGENT,
            'label'                       => $def->getTitle(),
            'data'                        => $data,
            'custom_per_field_context'    => $field_context,
            'custom_per_field_definition' => $def,
            'mapped'                      => false,
        ];

        if ($ignore_validation) {
            $options = $this->markNoValidation($options);
        }

        $context->getForm()->add(
            $field->getId(),
            'deskpro_custom_per_field_data',
            $options
        );
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addCategory(TicketFormContext $context, LayoutField $field)
    {
        if (!$this->canCategoryBeDisplayed($context)) {
            return;
        }

        $default = $context->getSetting('core.default_ticket_cat', null);
        if ($default) {
            if (!$context->getTicket()->getCategoryId()) {
                $context->getTicket()->setCategoryId($default);
            }
        }

        $context->getForm()->add($field->getId(), 'deskpro_category', [
            'label'       => $this->phrase('portal.forms.label_category'),
            'placeholder' => '',
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addPriority(TicketFormContext $context, LayoutField $field)
    {
        if (!$this->canPriorityBeDisplayed($context)) {
            return;
        }

        $default = $context->getSetting('core.default_ticket_pri', null);
        if ($default) {
            if (!$context->getTicket()->getPriorityId()) {
                $context->getTicket()->setPriorityId($default);
            }
        }

        $context->getForm()->add($field->getId(), 'deskpro_priority', [
            'label'       => $this->phrase('portal.forms.label_priority'),
            'placeholder' => '',
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addWorkflow(TicketFormContext $context, LayoutField $field)
    {
        if (!$this->canWorkflowBeDisplayed($context)) {
            return;
        }

        $default = $context->getSetting('core.default_ticket_work', null);
        if ($default) {
            if (!$context->getTicket()->getWorkflowId()) {
                $context->getTicket()->setWorkflowId($default);
            }
        }

        $context->getForm()->add($field->getId(), 'deskpro_workflow', [
            'label' => $this->phrase('portal.forms.label_workflow'),
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addProduct(TicketFormContext $context, LayoutField $field)
    {
        if (!$this->canProductBeDisplayed($context)) {
            return;
        }

        $default = $context->getSetting('core.default_prod_id', null);
        if ($default) {
            if (!$context->getTicket()->getProductId()) {
                $context->getTicket()->setProductId($default);
            }
        }

        $context->getForm()->add($field->getId(), 'deskpro_product', [
            'placeholder' => '',
        ]);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     * @param bool|false        $ignore_validation
     */
    private function addCaptcha(TicketFormContext $context, LayoutField $field, $ignore_validation = false)
    {
        if (!$this->canCaptchaBeDisplayed($context)) {
            return;
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

        $context->getForm()->add($field->getId(), 'deskpro_captcha', $options);
        $context->setCaptchaExistsOnForm(true);
    }

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     */
    private function addCc(TicketFormContext $context, LayoutField $field)
    {
        $context->getForm()->add($field->getId(), 'deskpro_cc', [
            'label'    => $this->phrase('portal.forms.label_cc'),
            'ticket'   => $context->getTicket(),
            'mapped'   => false,
            'required' => false,
        ]);
    }

    /**
     * @param TicketFormContext $context
     */
    private function addAttach(TicketFormContext $context)
    {
        if (!$context->getMessage()) {
            return;
        }

        $form = $context->getForm();
        $form
            ->add('attachments', 'ticket_message_attachment_collection', [
                'property_path'  => 'messages[0].attachments',
                'required'       => false,
                'person'         => $context->getPerson(),
                'ticket_message' => $context->getMessage(),
            ])
            ->add('more_attachments', 'submit', [
                'validation_groups' => false,
                'label'             => $this->phrase('portal.forms.label_add_attachment'),
            ])
        ;
    }

    /**
     * @param TicketFormContext $context
     */
    private function addSubmit(TicketFormContext $context)
    {
        if ($context->getVisibility() !== TicketFormContext::VISIBILITY_NEW) {
            $label = $this->phrase('portal.forms.label_save');
        } else {
            $label = $this->phrase('portal.forms.label_submit');
        }

        $context->getForm()->add('submit', 'submit', [
            'label' => $label,
        ]);
    }

    /**
     * @param FormInterface $form
     * @param Ticket        $ticket
     * @param TicketLayout  $initial_layout
     * @param array         $already_displayed_fields
     *
     * @return TicketFormContext
     */
    private function createTicketFormContext(FormInterface $form, Ticket $ticket, TicketLayout $initial_layout, $already_displayed_fields = [])
    {
        return new TicketFormContext($form, $ticket, $initial_layout, $already_displayed_fields);
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

    /**
     * @param TicketFormContext $context
     * @param LayoutField       $field
     *
     * @return bool
     */
    private function fieldWasDisplayedBefore(TicketFormContext $context, LayoutField $field)
    {
        return in_array($field->getId(), $context->getPreviouslyDisplayedFields());
    }

    /**
     * @param LayoutField $field
     * @param $extracted_data
     *
     * @return bool
     */
    private function fieldHasCriteriaAndCriteriaDoesNOTMatch(LayoutField $field, $extracted_data)
    {
        return $field->getCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted_data);
    }

    /**
     * @param LayoutField $field
     * @param $extracted_data
     *
     * @return bool
     */
    private function fieldHasCriteriaAndItDOESMatch(LayoutField $field, $extracted_data)
    {
        return $field->getCriteria() && $field->getCriteria()->isSubmittedDataMatch($extracted_data);
    }

    /**
     * @param $has_field_criteria
     * @param LayoutField $field
     * @param $extracted_data
     *
     * @return bool
     */
    private function fieldDoesNotHaveCriteriaOrHasCriteriaAndMatches($has_field_criteria, LayoutField $field, $extracted_data)
    {
        return !$has_field_criteria
        ||
        ($has_field_criteria && $field->getCriteria()->isSubmittedDataMatch($extracted_data));
    }

    /**
     * @param LayoutField       $field
     * @param TicketFormContext $context
     *
     * @return bool
     */
    private function shouldFieldBeSkipped(LayoutField $field, TicketFormContext $context)
    {
        switch ($field->getFieldType()) {
            case FormFields::PRIORITY:
                return !$this->canPriorityBeDisplayed($context);
            case FormFields::PRODUCT:
                return !$this->canProductBeDisplayed($context);
            case FormFields::WORKFLOW:
                return !$this->canWorkflowBeDisplayed($context);
            case FormFields::CATEGORY:
                return !$this->canCategoryBeDisplayed($context);
            case FormFields::CAPTCHA:
                return !$this->canCaptchaBeDisplayed($context);
            default:
                return false;
        }
    }

    /**
     * @param Layout            $new_layout
     * @param TicketFormContext $context
     * @param array             $extracted_data
     * @param array             $fields_to_remove
     * @param array             $additional_fields
     *
     * @return array
     */
    private function useLayoutCriteriaToDetermineDynamicLayoutChanges(Layout $new_layout, TicketFormContext $context, $extracted_data, $fields_to_remove, $additional_fields)
    {
        // DEPENDENT FIELDS
        // find fields that should be rendered, but weren't before, via criteria with recently submitted data
        $fields_requiring_rerender = [];
        foreach ($new_layout->all() as $field) {
            if ($this->shouldFieldBeSkipped($field, $context)) {
                continue;
            }
            if ($this->fieldWasDisplayedBefore($context, $field)) {
                // this field was displayed before. should it continue to be displayed?
                if ($this->fieldHasCriteriaAndCriteriaDoesNOTMatch($field, $extracted_data)) {
                    $fields_to_remove[] = $field;
                } elseif ($this->fieldHasCriteriaAndItDOESMatch($field, $extracted_data)) {
                    // this field was displayed before + is still supposed to be on the form after the submit
                    if (!$context->hasValidVisibility($field)) {
                        continue;
                    }

                    $this->addField($context, $field);
                }
            } else {
                // this field was not displayed before, but should it be added and the form re-rendered?
                $has_field_criteria = $field->getCriteria();
                if ($this->fieldDoesNotHaveCriteriaOrHasCriteriaAndMatches($has_field_criteria, $field, $extracted_data)) {
                    if (!in_array($field, $additional_fields)) {
                        $additional_fields[] = $field;
                    }
                    $fields_requiring_rerender[] = $field;
                }
            }
        }

        return [$fields_requiring_rerender, $fields_to_remove, $additional_fields];
    }

    /**
     * @param TicketFormContext $context
     *
     * @return bool
     */
    private function canProductBeDisplayed(TicketFormContext $context)
    {
        if (!$context->getSetting('core.use_product', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\Product $repository */
        $repository = $this->em->getRepository('DeskPRO:Product');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketFormContext $context
     *
     * @return bool
     */
    private function canPriorityBeDisplayed(TicketFormContext $context)
    {
        if (!$context->getSetting('core.use_ticket_priority', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketPriority $repository */
        $repository = $this->em->getRepository('DeskPRO:TicketPriority');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketFormContext $context
     *
     * @return bool
     */
    private function canCategoryBeDisplayed(TicketFormContext $context)
    {
        if (!$context->getSetting('core.use_ticket_category', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketCategory $repository */
        $repository = $this->em->getRepository('DeskPRO:TicketCategory');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketFormContext $context
     *
     * @return bool
     */
    private function canWorkflowBeDisplayed(TicketFormContext $context)
    {
        if (!$context->getSetting('core.use_ticket_workflow', false)) {
            return false;
        }

        /** @var \Application\DeskPRO\EntityRepository\TicketWorkflow $repository */
        $repository = $this->em->getRepository('DeskPRO:TicketWorkflow');

        return $repository->countAll() > 0;
    }

    /**
     * @param TicketFormContext $context
     *
     * @return bool
     */
    private function canCaptchaBeDisplayed(TicketFormContext $context)
    {
        if (!$context->getOption('use_captcha')) {
            return false;
        }

        // ensure captcha is only present once
        if ($context->doesCaptchaExistOnForm()) {
            return false;
        }

        return true;
    }
}
