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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\TicketLayout\LayoutField;
use Application\FormBundle\Form\FormFieldManager;
use Application\FormBundle\Form\TicketFormContext;
use Application\FormBundle\FormFields;
use Application\FormBundle\TicketLayout\TicketLayoutFactory;
use Application\FormBundle\Validator\Constraints\ValidCaptcha;
use Application\PersonBundle\Person\Context\CreatePersonContext;
use Application\PersonBundle\Person\PersonFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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

    public function __construct(FormFieldManager $field_manager, TicketLayoutFactory $ticket_layout_factory)
    {
        $this->field_manager = $field_manager;
        $this->ticket_layout_factory = $ticket_layout_factory;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreData'));
        $builder->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }

    public function onPostSubmit(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $event->getData();
        $form = $event->getForm();

        if ($form->has('message')) {
            //TODO: move this too message type
            $ticket->addMessage($event->getForm()->get('message')->getData());
        }
    }

    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $ticket */
        $ticket = $event->getData();
        $form = $event->getForm();
        $config = $form->getConfig();
        $layout = $this->ticket_layout_factory->getLayoutForTicketForm($ticket->department ?: null);

        $context = new TicketFormContext(
            $form,
            $ticket,
            $config->getOption('person'),
            $layout,
            $config->getOption('ticket_view_context'),
            $config->getOption('ticket_visibility')
        );

        foreach ($context->getActiveLayout()->all() as $field) {
            if (!$context->hasValidVisibility($field)) {
                continue;
            }

            if ($field->hasCriteria() && !$field->getCriteria()->isTicketMatch($ticket)) {
                continue;
            }

            $this->addField($context, $field);
        }
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'ticket_visibility'   => TicketFormContext::VISIBILITY_NEW,
            'ticket_view_context' => TicketFormContext::VIEW_USER,
            'data_class'          => 'Application\\DeskPRO\\Entity\\Ticket',
            'method'              => 'POST'
        ));
        $resolver->setRequired(array(
            'person'
        ));
        $resolver->addAllowedValues(array(
            'ticket_visibility' => array(
                TicketFormContext::VISIBILITY_NEW,
                TicketFormContext::VISIBILITY_EDIT,
                TicketFormContext::VISIBILITY_VIEW
            )
        ));
        $resolver->setAllowedTypes(array(
            'person'        => 'Application\\DeskPRO\\Entity\\Person'
        ));
    }


    public function getName()
    {
        return 'deskpro_ticket';
    }

    private function addField(TicketFormContext $form_context, LayoutField $field)
    {
        switch ($field->getFieldType()) {

            case FormFields::SUBJECT:
                $this->addSubject($form_context, $field);
                break;
            case FormFields::MESSAGE:
                $this->addMessage($form_context, $field);
                break;
            case FormFields::DEPARTMENT:
                $this->addDepartment($form_context, $field);
                break;
            case FormFields::CATEGORY:
                $this->addCategory($form_context, $field);
                break;
            case FormFields::PRIORITY:
                $this->addPriority($form_context, $field);
                break;
            case FormFields::WORKFLOW:
                $this->addWorkflow($form_context, $field);
                break;
            case FormFields::PRODUCT:
                $this->addProduct($form_context, $field);
                break;
            case FormFields::CAPTCHA:
                $this->addCaptcha($form_context, $field);
                break;
            case FormFields::CC:
                $this->addCc($form_context, $field);
                break;
            case FormFields::ATTACH:
                $this->addAttach($form_context, $field);
                break;
            case FormFields::USER_EMAIL:
                $this->addUserEmail($form_context, $field);
                break;
            case FormFields::USER_NAME:
                $this->addUserName($form_context, $field);
                break;
            case FormFields::USER_TIMEZONE:
                $this->addUserTimezone($form_context, $field);
                break;
            case FormFields::USER_LANGUAGE:
                $this->addUserLanguage($form_context, $field);
                break;
            case FormFields::USER_FIELD:
                $this->addCustomUserField($form_context, $field);
                break;
            case FormFields::TICKET_FIELD:
                $this->addCustomTicketField($form_context, $field);
                break;
            case FormFields::CUSTOM_FIELD:
                $this->addCustomCustomField($form_context, $field);
                break;

        }
    }

    private function addDepartment(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('department', 'deskpro_department', array(
            'person' => $form_context->getPerson()
        ));
    }

    private function addSubject(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('subject', 'text', array(
            'required' => true,
            'constraints' => array(
                new NotBlank(array('message' => 'This value is required')),
                new Length(array('min' => 5, 'minMessage' => 'The subject must be at least 5 characters in length'))
            )
        ));
    }

    private function addMessage(TicketFormContext $form_context, LayoutField $field)
    {
        if (TicketFormContext::VISIBILITY_NEW !== $form_context->getVisibility()) {
            return;
        }

        $form_context->getForm()->add('message', 'deskpro_ticket_message', array(
            'mapped' => false,
            'label'  => false,
            'person' => $form_context->getPerson(),
            'ticket' => $form_context->getTicket()
        ));
    }

    private function addUserEmail(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('email', 'email', array(
            'property_path' => 'person.primary_email',
            'constraints' => array(
                new NotBlank(array('message' => 'Please provide us with your email')),
                new Email(array('message' => 'This email adddress is not valid')),
            )
        ));
    }

    private function addUserTimezone(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('timezone', 'timezone', array(
            'property_path' => 'person.timezone'
        ));
    }

    private function addUserName(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('name', 'text', array(
            'property_path' => 'person.name',
            'empty_data'    => $form_context->getPerson()->getName()
        ));
    }

    private function addUserLanguage(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('language', 'deskpro_language', array(
            'property_path' => 'person.language',
            'view_context'  => $form_context->getViewContext()
        ));
    }

    private function addCustomTicketField(TicketFormContext $form_context, LayoutField $field)
    {
        $field_def = $this->field_manager->getCustomTicketFieldById($field->getFieldId(), $form_context);

        if (!$field_def->is_enabled) {
            return false;
        }

        $form_context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_ticket',
            array(
                'custom_data_field' => $field_def,
                'ticket' => $form_context->getTicket(),
                'property_path' => sprintf('getCustomDataCollection[%s]', $field->getFieldId()),
                'agent_interface' => $form_context->getViewContext() === TicketFormContext::VIEW_AGENT,
                'label'           => false
            )
        );
    }

    private function addCustomUserField(TicketFormContext $form_context, LayoutField $field)
    {
        $field_def = $this->field_manager->getCustomPersonFieldById($field->getFieldId());

        if (!$field_def->is_enabled) {
            return false;
        }

        $form_context->getForm()->add(
            $field->getId(),
            'deskpro_custom_data_person',
            array(
                'custom_data_field' => $field_def,
                'person'            => $form_context->getPerson(),
                'property_path'     => sprintf('person.getCustomDataCollection[%s]', $field->getFieldId()),
                'agent_interface' => $form_context->getViewContext() === TicketFormContext::VIEW_AGENT,
                'label'           => false
            )
        );
    }

    private function addCustomCustomField(TicketFormContext $form_context, LayoutField $field)
    {
    }

    private function addCategory(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('category', 'deskpro_category', array());
    }

    private function addPriority(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('priority', 'deskpro_priority', array());
    }

    private function addWorkflow(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('workflow', 'deskpro_workflow', array());
    }

    private function addProduct(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('product', 'deskpro_product', array());
    }

    private function addCaptcha(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('captcha', 'deskpro_captcha', array(
            'mapped'      => false,
            'error_bubbling' => false,
            'constraints' => array(
                new ValidCaptcha()
            )
        ));
    }

    private function addCc(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('cc', 'deskpro_cc', array(
            'ticket' => $form_context->getTicket(),
            'mapped' => false,
            'required' => false
        ));
    }

    private function addAttach(TicketFormContext $form_context, LayoutField $field)
    {
        $form_context->getForm()->add('attach', 'deskpro_attach', array('mapped' => false, 'required' => false));
    }
}
