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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyGenerator;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDisableAutoProcessListener;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\FieldRendererInterface;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\AbstractFieldResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
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
     * @var FormFactory
     */
    private $formFactory;

    /**
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchyGenerator
     * @param FormFactory        $formFactory
     */
    public function __construct(HierarchyGenerator $hierarchyGenerator, FormFactory $formFactory)
    {
        $this->hierarchyGenerator = $hierarchyGenerator;
        $this->formFactory        = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit'], 200);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onUpdateRelatedData']);
        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('full_type_class')
            ->setAllowedTypes('full_type_class', 'string')
            ->setRequired(['field_resolver', 'field_renderer', 'layout_factory'])
            ->setAllowedTypes('field_resolver', AbstractFieldResolver::class)
            ->setAllowedTypes('field_renderer', FieldRendererInterface::class)
            ->setAllowedTypes('layout_factory', 'callable')
            ->setDefault('form_type', null)
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
        $context = TicketWithLayoutsContext::createOnPreSetData($event);
        TicketLayoutHelper::renderFormFields($context, function (LayoutField $field) use ($context) {
            return $field->getCriteria()->isTicketMatch($context->getTicket());
        });
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $form->getData();
        $context = TicketWithLayoutsContext::createOnPreSubmit($event);

        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        if ($data) {
            $ticket->setDepartment($data->getDepartment());
        }

        $options         = $form->getConfig()->getOptions();
        $fullFormOptions = [
            'person'              => $options['person'],
            'ticket_view_context' => $options['ticket_view_context'],
            'ticket_visibility'   => $options['ticket_visibility'],
        ];

        $fullForm = $this->formFactory->create($options['full_type_class'], $ticket, $fullFormOptions);
        $fullForm->submit($event->getData());

        TicketLayoutHelper::renderFormFields($context, function (LayoutField $field) use ($ticket) {
            return $field->getCriteria()->isTicketMatch($ticket);
        });
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
        $ticketMessage = $ticket->messages->first();
        if ($ticketMessage instanceof TicketMessage) {
            $person = $ticket->getPerson();
            $ticketMessage->setPerson($person);

            foreach ($ticketMessage->getAttachments() as $attachment) {
                $attachment->setPerson($person);
            }
        }
    }
}
