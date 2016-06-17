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

use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDisableAutoProcessListener;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\FieldRendererInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\AbstractFieldResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketWithLayoutsManipulatorType.
 */
class TicketWithLayoutsManipulatorType extends AbstractType
{
    /**
     * @var HierarchyGenerator
     */
    private $hierarchyGenerator;

    /**
     * @var TicketLayoutHelper
     */
    private $layoutHelper;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchyGenerator
     * @param TicketLayoutHelper $layoutHelper
     */
    public function __construct(HierarchyGenerator $hierarchyGenerator, TicketLayoutHelper $layoutHelper)
    {
        $this->hierarchyGenerator = $hierarchyGenerator;
        $this->layoutHelper       = $layoutHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onUpdateRelatedData']);
        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'allow_rerender' => false,
            ])
            ->setRequired(['field_resolver', 'field_renderer', 'layout_factory'])
            ->setAllowedTypes([
                'field_resolver' => AbstractFieldResolver::class,
                'field_renderer' => FieldRendererInterface::class,
                'layout_factory' => 'callable',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketWithLayoutsType::class;
    }

    /**
     * Create the form based on ticket department.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\Ticket $data */
        $data    = $event->getData();
        $form    = $event->getForm();
        $options = $form->getConfig()->getOptions();

        // Setting ticket person if not defined
        if (!$data->getPerson()) {
            $data->setPerson($options['person']);
        }

        // if there is only one department we want to make sure to set it now...
        $hierarchy = $this->hierarchyGenerator->generateTicketDepartmentsHierarchy($options['person']);

        // if there is only one dep, and ticket has no dep, just set it on the ticket (we won't be showing the widget)
        if (!$data->getDepartment()) {
            if ($hierarchy->countSelectable() === 1) {
                $data->setDepartment($hierarchy->getFirstSelectable());
            }
        }

        $context = TicketWithLayoutsContext::createOnPreSetData($event);
        $changes = $this->layoutHelper->getLayoutChanges($context);

        foreach ($changes->getAdditionalFields() as $field) {
            if ($field->hasCriteria() && !$field->getCriteria()->isTicketMatch($context->getTicket())) {
                continue;
            }

            $formField = $context->getFieldResolver()->createFormField($context, $field);
            if ($formField) {
                $context->getFieldRenderer()->addField($context, $field, $formField);
            }
        }
    }

    /**
     * Change the form based on submitted department.
     *
     * @internal
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
            if ($ticket->getDepartment()->getId() != $data[FormFields::DEPARTMENT]) {
                // if department was changed, we need to clear its related data
                $ticket->resetCustomData();
            }
        }

        $context   = TicketWithLayoutsContext::createOnPreSubmit($event);
        $extracted = $this->layoutHelper->getExtractedData($data, $context);
        $changes   = $this->layoutHelper->getLayoutChanges($context, $extracted);

        foreach ($changes->getAdditionalFields() as $field) {
            if ($field->hasCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted)) {
                continue;
            }

            $formField = $context->getFieldResolver()->createFormField($context, $field);
            if ($formField) {
                $context->getFieldRenderer()->addField($context, $field, $formField);
            }
        }

        foreach ($changes->getFieldsToRemove() as $field) {
            $context->getFieldRenderer()->removeField($context, $field);
        }
    }

    /**
     * Update related ticket data after submission.
     *
     * @internal
     *
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
}
