<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\WebFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\WebFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketWithLayoutsWebType.
 */
class TicketWithLayoutsWebType extends AbstractType
{
    /**
     * @var TicketLayoutFactory
     */
    private $layoutFactory;

    /**
     * @var WebFieldResolver
     */
    private $fieldResolver;

    /**
     * @var WebFieldRenderer
     */
    private $fieldRenderer;

    /**
     * Constructor.
     *
     * @param WebFieldResolver    $fieldResolver
     * @param WebFieldRenderer    $fieldRenderer
     * @param TicketLayoutFactory $layoutFactory
     */
    public function __construct(WebFieldResolver $fieldResolver, WebFieldRenderer $fieldRenderer, TicketLayoutFactory $layoutFactory)
    {
        $this->fieldResolver = $fieldResolver;
        $this->fieldRenderer = $fieldRenderer;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketWithLayoutsManipulatorType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ticket';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onAddUiFields'], -1);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddUiFields'], -1);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddRerenderField'], -1);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onResetRerenderFormData'], 100);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onHandleCustomSubject'], 210);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['visibility'] = $options['ticket_visibility'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'use_captcha'           => true,
            'hide_department_field' => false,
            'subject_type'          => null,
            'default_subject'       => null,
            'field_resolver'        => $this->fieldResolver,
            'field_renderer'        => $this->fieldRenderer,
            'full_type_class'       => TicketWithLayoutsWebFullType::class,
            'layout_factory'        => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, false);
            },
            'constraints' => [
                new AppAssert\Ticket\TicketDupe(),
            ],

            // allow extra fields for the new ticket form to prevent inability to submit the form
            // for unexpected layout manipulations
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * Add web specific form fields.
     *
     * @internal
     *
     * @param FormEvent $event
     * @param string    $eventName
     */
    public function onAddUiFields(FormEvent $event, $eventName)
    {
        if ($eventName === FormEvents::PRE_SUBMIT) {
            $context = TicketWithLayoutsContext::createOnPreSubmit($event);
        } else {
            $context = TicketWithLayoutsContext::createOnPreSetData($event);
        }

        $this->fieldRenderer->addDisplayFields($context);
        $this->fieldRenderer->addSubmitButton($context);
    }

    /**
     * Add re-render field to display re-render alert.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onAddRerenderField(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $context = TicketWithLayoutsContext::createOnPreSubmit($event);

        $hasNotSubmitted = false;

        $displayedFields = [];
        if (isset($data['displayed_fields'])) {
            if (is_string($data['displayed_fields'])) {
                $data['displayed_fields'] = explode(',', $data['displayed_fields']);
            }
            $displayedFields = array_flip($data['displayed_fields']);
        }

        foreach (TicketLayoutHelper::getLayoutFields($context) as $field) {
            // the form was already updated via the form manipulator pre submit callback
            // so check if the field should be rendered based on the rendered form
            if (!$form->has($field->getId())) {
                continue;
            }

            // check if there was submitted data for this field and that the field wasn't on the previous layout
            $notSubmitted   = !array_key_exists($field->getId(), $data);
            $wasntDisplayed = !isset($displayedFields[$field->getId()]);

            if ($notSubmitted && $wasntDisplayed && !$context->fieldWasDisplayedBefore($field)) {
                $hasNotSubmitted = true;
            }
        }

        // we signal to the controller that we want to re-render (and NOT submit or process) by adding a hidden field
        if ($context->hadLayout() && $hasNotSubmitted && $hasNotSubmitted && count($data) > 0) {
            $this->fieldRenderer->addRerenderField($context);
        }
    }

    /**
     * Reset request data of temp field to avoid extra field validation error.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onResetRerenderFormData(FormEvent $event)
    {
        $data = $event->getData();
        if (array_key_exists('rerender_form', $data)) {
            unset($data['rerender_form']);
        }

        $event->setData($data);
    }

    /**
     * custom subject based on Admin settings or 5 first words.
     *
     * @param FormEvent $event
     */
    public function onHandleCustomSubject(FormEvent $event)
    {
        $data    = $event->getData();
        $context = TicketWithLayoutsContext::createOnPreSubmit($event);
        if ($context->getOption('subject_type') === 'default') {
            $data['subject'] = $context->getOption('default_subject');
            $event->setData($data);
        }

        if ($context->getOption('subject_type') === 'message') {
            $message         = trim(strip_tags(html_entity_decode(@$data['message']['message'])));
            $parts           = preg_split('/[\s]+/', $message);
            $data['subject'] = implode(' ', array_slice($parts, 0, 5));
            $event->setData($data);
        }
    }
}
