<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\FormBundle\Form\Type;

use Application\AppBundle\Hierarchy\HierarchyNode;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Application\FormBundle\Form\FormFieldManager;
use Application\FormBundle\Form\TicketFormContext;
use Application\FormBundle\FormFields;
use Application\FormBundle\Hierarchy\HierarchyGenerator;
use Application\FormBundle\TicketLayout\TicketLayoutDiffer;
use Application\FormBundle\TicketLayout\TicketLayoutFactory;
use Application\FormBundle\Validator\Constraints\ValidCaptcha;
use Application\LanguageBundle\Language\LanguageManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TicketType extends AbstractType
{
    /**
     * @var \Application\FormBundle\Form\FormFieldManager
     */
    private $field_manager;

    /**
     * @var \Application\FormBundle\TicketLayout\TicketLayoutFactory
     */
    private $ticket_layout_factory;

    /**
     * @var \Application\FormBundle\TicketLayout\TicketLayoutDiffer
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
     * @var LanguageManager
     */
    private $language_manager;

    public function __construct(
        FormFieldManager $field_manager,
        TicketLayoutFactory $ticket_layout_factory,
        TicketLayoutDiffer $layout_differ,
        HierarchyGenerator $hierarchy_generator,
        EntityManager $em,
        LanguageManager $language_manager
    )
    {
        $this->layout_differ = $layout_differ;
        $this->field_manager = $field_manager;
        $this->ticket_layout_factory = $ticket_layout_factory;
        $this->hierarchy_generator = $hierarchy_generator;
        $this->em = $em;
        $this->language_manager = $language_manager;
    }

    /**
     * The entire Ticket Form is created using listeners. Nothing exists by default.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    /**
     * PRE DATA PROCESSING (creates the form based on ticket department)
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $event->getData();
        $form = $event->getForm();
        $ticket_message = $form->getConfig()->getOption('ticket_message');
        $layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->department ?: null);
        $context = $this->createTicketFormContext($ticket, $ticket_message, $form, $layout);

        // if there is only one department we want to make sure to set it now...
        $person = $context->getForm()->getConfig()->getOption('person');
        $hierarchy = $this->hierarchy_generator->generateTicketDepartmentsHierarchy($person);
        if ($hierarchy->countSelectable() === 1) {
            $ticket->department = $hierarchy->getFirstSelectable();
        }

        $this->manipulateForm(new Layout(), $context->getActiveLayout(), $context);
    }

    /**
     * PRE SUBMIT PROCESSING (the data we get here is a pure array of submitted values) (we then manipulate the form if dep changes)
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $event->getForm()->getData();
        $form = $event->getForm();
        $ticket_message = $form->getConfig()->getOption('ticket_message');
        $pre_submit_data = $event->getData();

        // calculate the initial layout of the form (before any form submissions took place)
        $layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->department ?: null);
        $context = $this->createTicketFormContext($ticket, $ticket_message, $form, $layout);
        $initial_layout = $context->getActiveLayout();

        // by default, we treat a submit as a "potentially_rerender", but it wont actually rerender unless
        // new fields are added to the form.
        $potentially_rerender_form = true;

        // now we need to compare the department's layout and see if we need to add/remove fields before we submit data
        if ($form->has(FormFields::DEPARTMENT) && isset($pre_submit_data[FormFields::DEPARTMENT])) {

            //
            // here we just get the submitted department id (its not the same as submitted value due to choice lists)
            //
            /** @var \Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList $choice_list */
            // choice types dont submit entity IDS, they submit choice list IDs. So we need to calc the real entity Id.
            $extracted_data = $this->getTicketDataIds($pre_submit_data, $context);
            $new_department_id = $extracted_data['department'];
            //
            // end get new department id
            //

            //
            // Re-render detection
            //
            // determine if we actually changed department between requests
            // this keeps state in a hidden form variable so we know if we
            // are using the original dep, and need to rerender, OR if we
            // are on multiple re-renders...
            // we do this because we only disable validation on new fields
            // the first time a department is changed.
            // Errors must still render on subsequent requests!
            // set potentially_rerender = false if we shouldnt treat this as a rerender
            if (array_key_exists('last_department_id', $pre_submit_data)) {
                $last_department_id = $pre_submit_data['last_department_id'];

                // there wasn't actually a change in department between requests. treat validation as if we didnt just
                // rerender the form
                if ($new_department_id == $last_department_id) {
                    $potentially_rerender_form = false;
                }
            }
            if ($context->getForm()->has('last_department_id')) {
                $context->getForm()->remove('last_department_id');
            }
            $context->getForm()->add('last_department_id', 'hidden', array('mapped' => false, 'label' => false));
            $event->setData(array_merge($pre_submit_data, array('last_department_id' => $new_department_id)));
            //
            // end rerender detection
            //



            $destination_layout = $this->ticket_layout_factory->getLayoutForTicketForm($new_department_id ?: null);
            $context->setNewLayout($destination_layout);
        }

        $this->manipulateForm($initial_layout, $context->getActiveLayout(), $context, $pre_submit_data, $potentially_rerender_form);
    }

    /**
     * @param Layout $initial_layout
     * @param Layout $new_layout
     * @param TicketFormContext $context
     * @param array $submitted_data
     * @param bool $potentially_rerender_form
     */
    protected function manipulateForm(Layout $initial_layout, Layout $new_layout, TicketFormContext $context, $submitted_data = array(), $potentially_rerender_form = false)
    {
        $additional_fields = $this->layout_differ->findFieldsToAdd($initial_layout, $new_layout);
        $fields_to_remove = $this->layout_differ->findFieldsToRemove($initial_layout, $new_layout);

        $extracted_data = $this->getTicketDataIds($submitted_data, $context);

        $added_something = false;
        foreach ($additional_fields as $field) {
            if (!$context->hasValidVisibility($field)) {
                continue;
            }

            if (count($submitted_data)) {
                if ($field->hasCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted_data)) {
                    continue;
                }
            } else {
                if ($field->hasCriteria() && !$field->getCriteria()->isTicketMatch($context->getTicket())) {
                    continue;
                }
            }

            // if something is added, we need to ensure "submit" is removed (it's re-added at the end, below)
            if ($context->getForm()->has('submit')) {
                $context->getForm()->remove('submit');
            }

            // we signal to the controller that we want to rerender (and NOT submit or process) by adding a hidden field
            if ($potentially_rerender_form && count($submitted_data) > 0 && !$context->getForm()->has('rerender_form')) {
                $context->getForm()->add('rerender_form', 'hidden', array('mapped' => false, 'label' => false));
            }

            $added_something = true;

            $this->addField($context, $field, $potentially_rerender_form);
        }

        foreach ($fields_to_remove as $field) {
            $this->removeField($context, $field);
        }

        if (!$added_something && $context->getForm()->has('rerender_form')) {
            // we didn't add anything new, so remove the signal a re-render
            $context->getForm()->remove('rerender_form');
        }

        $this->addSubmit($context);
    }

    /**
     * Submitted choice values are not submitted with the entity Id. Instead we are given the choice list key.
     *
     * This inspects the submitted data on our form and gives us data we're interesed in.
     *
     * @param array $submitted_data
     * @param TicketFormContext $context
     * @return array the form key and its selected entity ID (or null if not submitted)
     */
    private function getTicketDataIds(array $submitted_data, TicketFormContext $context)
    {
        $form = $context->getForm();

        $final_data = array();

        $keys = array(FormFields::DEPARTMENT, FormFields::PRODUCT, FormFields::CATEGORY, FormFields::WORKFLOW, FormFields::PRIORITY);

        foreach ($keys as $key) {
            if (array_key_exists($key, $submitted_data)) {
                $submitted_value = $submitted_data[$key];
                $choice = current($form->get($key)->getConfig()->getOption('choice_list')->getChoicesForValues(array($submitted_value)));

                if ($choice instanceof HierarchyNode) {
                    $choice = $choice->getData();
                }

                $final_data[$key] = $choice->getId();
            } else {
                $final_data[$key] = null;
            }
        }

        return $final_data;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'ticket_visibility'   => TicketFormContext::VISIBILITY_NEW,
            'ticket_view_context' => TicketFormContext::VIEW_USER,
            'data_class'          => 'Application\\DeskPRO\\Entity\\Ticket',
            'method'              => 'POST',
            'allow_extra_fields'  => true,
            'ticket_message'      => null
        ));
        $resolver->setRequired(array(
            'person',
            'settings'
        ));
        $resolver->addAllowedValues(array(
            'ticket_visibility' => array(
                TicketFormContext::VISIBILITY_NEW,
                TicketFormContext::VISIBILITY_EDIT,
                TicketFormContext::VISIBILITY_VIEW
            )
        ));
        $resolver->setAllowedTypes(array(
            'person'        => 'Application\\DeskPRO\\Entity\\Person',
            'settings'        => 'Application\\DeskPRO\\NewSettings\\SettingsBag',
            'ticket_message' => array('Application\\DeskPRO\\Entity\\TicketMessage', 'null')
        ));
    }


    public function getName()
    {
        return 'ticket';
    }

    private function removeField(TicketFormContext $form_context, LayoutField $field)
    {
        if (!$form_context->getForm()->has($field->getId())) {
            return;
        }

        $form_context->getForm()->remove($field->getId());
    }

    private function addField(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        if ($form_context->getForm()->has($field->getId())) {
            return;
        }

        switch ($field->getFieldType()) {

            case FormFields::SUBJECT:
                $this->addSubject($form_context, $field, $ignore_validation);
                break;
            case FormFields::MESSAGE:
                $this->addMessage($form_context, $field, $ignore_validation);
                break;
            case FormFields::DEPARTMENT:
                $this->addDepartment($form_context, $field, $ignore_validation);
                break;
            case FormFields::CATEGORY:
                $this->addCategory($form_context, $field, $ignore_validation);
                break;
            case FormFields::PRIORITY:
                $this->addPriority($form_context, $field, $ignore_validation);
                break;
            case FormFields::WORKFLOW:
                $this->addWorkflow($form_context, $field, $ignore_validation);
                break;
            case FormFields::PRODUCT:
                $this->addProduct($form_context, $field, $ignore_validation);
                break;
            case FormFields::CAPTCHA:
                $this->addCaptcha($form_context, $field, $ignore_validation);
                break;
            case FormFields::CC:
                $this->addCc($form_context, $field, $ignore_validation);
                break;
            case FormFields::ATTACH:
                $this->addAttach($form_context, $field, $ignore_validation);
                break;
            case FormFields::USER_EMAIL:
                $this->addUserEmail($form_context, $field, $ignore_validation);
                break;
            case FormFields::USER_NAME:
                $this->addUserName($form_context, $field, $ignore_validation);
                break;
            case FormFields::USER_TIMEZONE:
                $this->addUserTimezone($form_context, $field, $ignore_validation);
                break;
            case FormFields::USER_LANGUAGE:
                $this->addUserLanguage($form_context, $field, $ignore_validation);
                break;
            case FormFields::USER_FIELD:
                $this->addCustomUserField($form_context, $field, $ignore_validation);
                break;
            case FormFields::TICKET_FIELD:
                $this->addCustomTicketField($form_context, $field, $ignore_validation);
                break;

        }
    }

    private function addDepartment(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $person = $form_context->getForm()->getConfig()->getOption('person');
        $hierarchy = $this->hierarchy_generator->generateTicketDepartmentsHierarchy($person);

        // if it is 1 or less to choose from, dont even add this field to the form
        if ($hierarchy->countSelectable() <= 1) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_department', array(
            'person' => $form_context->getPerson()
        ));
    }

    private function addSubject(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $options = array(
            'label' => 'Subject',
            'required' => true,
            'constraints' => array(
                new NotBlank(array('message' => 'This value is required')),
                new Length(array('min' => 5, 'minMessage' => 'The subject must be at least 5 characters in length.'))
            )
        );

        if ($ignore_validation) {
            $options = $this->markNoValidation($form_context, $options);
        }

        $form_context->getForm()->add('plain_subject', 'text', $options);
    }

    private function addMessage(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        if (TicketFormContext::VISIBILITY_NEW !== $form_context->getVisibility()) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'ticket_message', array(
            'mapped' => false,
            'label'  => false,
            'person' => $form_context->getPerson(),
            'ticket' => $form_context->getTicket(),
            'data'   => $form_context->getMessage()
        ));
    }

    private function addUserEmail(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $form_context->getForm()->add($field->getId(), 'deskpro_person_email', array(
            'property_path' => 'person.primary_email',
            'label' => false
        ));
    }

    private function addUserTimezone(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $form_context->getForm()->add($field->getId(), 'timezone', array(
            'property_path' => 'person.timezone'
        ));
    }

    private function addUserName(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $form_context->getForm()->add($field->getId(), 'text', array(
            'property_path' => 'person.name',
            'label'         => 'Name',
            'empty_data'    => $form_context->getPerson()->getName()
        ));
    }

    private function addUserLanguage(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        if (!$this->language_manager->isMultiLanguagePortal()) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_language', array(
            'property_path' => 'person.language',
            'view_context'  => $form_context->getViewContext()
        ));
    }

    private function addCustomTicketField(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomTicketFieldById($field->getFieldId(), $form_context);

        if (!$field_def->is_enabled) {
            return false;
        }

        $options = array(
            'custom_data_field' => $field_def,
            'ticket' => $form_context->getTicket(),
            'property_path' => sprintf('getCustomDataCollection[%s]', $field->getFieldId()),
            'agent_interface' => $form_context->getViewContext() === TicketFormContext::VIEW_AGENT,
            'label' => false
        );

        if ($ignore_validation) {
            $options = $this->markNoValidation($form_context, $options);
            $options['ignore_validation'] = true;
        }

        $form_context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_ticket',
            $options
        );
    }

    private function addCustomUserField(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $field_def = $this->field_manager->getCustomPersonFieldById($field->getFieldId());

        if (!$field_def->is_enabled) {
            return false;
        }

        $options = array(
            'custom_data_field' => $field_def,
            'person' => $form_context->getPerson(),
            'property_path' => sprintf('person.getCustomDataCollection[%s]', $field->getFieldId()),
            'agent_interface' => $form_context->getViewContext() === TicketFormContext::VIEW_AGENT,
            'label' => false
        );

        if ($ignore_validation) {
            $options = $this->markNoValidation($form_context, $options);
            $options['ignore_validation'] = true;
        }

        $form_context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_person',
            $options
        );
    }

    private function addCategory(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        // we need the brand setting to be correct
        if (!$this->getSettingsBag($form_context->getForm())->get('core.use_ticket_category', false)) {
            return;
        }

        if (!$this->em->getRepository('DeskPRO:TicketCategory')->countAll() > 0) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_category', array());
    }

    private function addPriority(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        // we need the brand setting to be correct
        if (!$this->getSettingsBag($form_context->getForm())->get('core.use_ticket_priority', false)) {
            return;
        }

        if (!$this->em->getRepository('DeskPRO:TicketPriority')->countAll() > 0) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_priority', array());
    }

    private function addWorkflow(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        // we need the brand setting to be correct
        if (!$this->getSettingsBag($form_context->getForm())->get('core.use_ticket_workflow', false)) {
            return;
        }

        if (!$this->em->getRepository('DeskPRO:TicketWorkflow')->countAll() > 0) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_workflow', array());
    }

    private function addProduct(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        // we need the brand setting to be correct
        if (!$this->getSettingsBag($form_context->getForm())->get('core.use_product', false)) {
            return;
        }

        if (!$this->em->getRepository('DeskPRO:Product')->countAll() > 0) {
            return;
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_product', array());
    }

    private function addCaptcha(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $options = array(
            'mapped' => false,
            'error_bubbling' => false,
            'constraints' => array(
                new ValidCaptcha()
            )
        );

        if ($ignore_validation) {
            $options = $this->markNoValidation($form_context, $options);
        }

        $form_context->getForm()->add($field->getId(), 'deskpro_captcha', $options);
    }

    private function addCc(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        $form_context->getForm()->add($field->getId(), 'deskpro_cc', array(
            'ticket' => $form_context->getTicket(),
            'mapped' => false,
            'required' => false
        ));
    }

    private function addAttach(TicketFormContext $form_context, LayoutField $field, $ignore_validation = false)
    {
        if ($form_context->getMessage()) {
            $form_context->getForm()->add('attachments', 'ticket_message_attachment_collection', array(
                'property_path'  => 'messages[0].attachments',
                'required'       => false,
                'person'         => $form_context->getPerson(),
                'ticket_message' => $form_context->getMessage()
            ));
            $form_context->getForm()->add('more_attachments', 'submit', array(
                'validation_groups' => false,
                'label' => 'Add Another Attachment'
            ));
        }
    }

    private function addSubmit(TicketFormContext $form_context)
    {
        $form_context->getForm()->add('submit', 'submit', array(
            'label' => 'Submit Ticket'
        ));
    }

    /**
     * @param Ticket $ticket
     * @param TicketMessage $ticket_message
     * @param FormInterface $form
     * @param TicketLayout $initial_layout
     * @return TicketFormContext
     */
    private function createTicketFormContext(Ticket $ticket, TicketMessage $ticket_message = null, FormInterface $form, TicketLayout $initial_layout)
    {
        $config = $form->getConfig();

        return new TicketFormContext(
            $form,
            $ticket,
            $ticket_message,
            $config->getOption('person'),
            $initial_layout,
            $config->getOption('ticket_view_context'),
            $config->getOption('ticket_visibility')
        );
    }

    private function markNoValidation(TicketFormContext $form_context, array $options)
    {
        return array_merge($options, array(
            'validation_groups' => array(),
            'constraints' => array()
        ));
    }

    /**
     * @param FormInterface $form
     * @return \Application\DeskPRO\NewSettings\SettingsBag
     */
    private function getSettingsBag(FormInterface $form)
    {
        return $form->getConfig()->getOption('settings');
    }
}
