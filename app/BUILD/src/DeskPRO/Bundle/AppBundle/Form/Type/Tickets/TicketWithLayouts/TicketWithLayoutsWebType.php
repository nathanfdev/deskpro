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
     * @var TicketLayoutHelper
     */
    private $layoutHelper;

    /**
     * Constructor.
     *
     * @param WebFieldResolver    $fieldResolver
     * @param WebFieldRenderer    $fieldRenderer
     * @param TicketLayoutFactory $layoutFactory
     * @param TicketLayoutHelper  $layoutHelper
     */
    public function __construct(
        WebFieldResolver    $fieldResolver,
        WebFieldRenderer    $fieldRenderer,
        TicketLayoutFactory $layoutFactory,
        TicketLayoutHelper  $layoutHelper
    ) {
        $this->fieldResolver = $fieldResolver;
        $this->fieldRenderer = $fieldRenderer;
        $this->layoutFactory = $layoutFactory;
        $this->layoutHelper  = $layoutHelper;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onAddUIFields'], -1);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddUIFields'], -1);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddRerenderField'], -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_rerender' => true,
            'use_captcha'    => true,
            'field_resolver' => $this->fieldResolver,
            'field_renderer' => $this->fieldRenderer,
            'layout_factory' => function ($department) {
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
    public function onAddUIFields(FormEvent $event, $eventName)
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
        $data    = $event->getData();
        $context = TicketWithLayoutsContext::createOnPreSubmit($event);

        $hasNotSubmitted = false;
        $displayedFields = isset($data['displayed_fields']) ? array_flip(explode(',', $data['displayed_fields'])) : [];

        $extracted = $this->layoutHelper->getExtractedData($data, $context);
        $changes   = $this->layoutHelper->getLayoutChanges($context, $extracted);

        foreach ($changes->getAdditionalFields() as $field) {
            if ($field->hasCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted)) {
                continue;
            }

            $formField = $this->fieldResolver->createFormField($context, $field);
            if ($formField) {
                // check if there was submitted data for this field
                if (!array_key_exists($field->getId(), $data) && !isset($displayedFields[$field->getId()])) {
                    $hasNotSubmitted = true;
                }
            }
        }

        // we signal to the controller that we want to rerender (and NOT submit or process) by adding a hidden field
        if ($context->hadLayout() && $hasNotSubmitted && count($changes->getFieldsRequiringRerender()) > 0 && count($data) > 0) {
            $this->fieldRenderer->addRerenderField($context);
        }
    }
}
