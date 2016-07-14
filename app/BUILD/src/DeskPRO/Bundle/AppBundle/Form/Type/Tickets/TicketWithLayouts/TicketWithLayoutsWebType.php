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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\WebFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\WebFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'use_captcha'           => true,
            'hide_department_field' => false,
            'field_resolver'        => $this->fieldResolver,
            'field_renderer'        => $this->fieldRenderer,
            'layout_factory'        => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, false);
            },
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
        $displayedFields = isset($data['displayed_fields']) ? array_flip(explode(',', $data['displayed_fields'])) : [];

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
}
