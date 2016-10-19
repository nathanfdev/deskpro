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
use Application\DeskPRO\TicketLayout\LayoutField;
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
     * Constructor.
     *
     * @param HierarchyGenerator $hierarchyGenerator
     */
    public function __construct(HierarchyGenerator $hierarchyGenerator)
    {
        $this->hierarchyGenerator = $hierarchyGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onUpdateRelatedData'], 100);
        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
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
        TicketLayoutHelper::renderFormFields($context, function (LayoutField $field) use ($context) {
            return $field->getCriteria()->isTicketMatch($context->getTicket());
        });
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
        $context = TicketWithLayoutsContext::createOnPreSubmit($event);
        $form    = $event->getForm();

        if ($context->getOption('subject_type') === 'default') {
            $data            = $event->getData();
            $data['subject'] = $context->getOption('default_subject');
            $event->setData($data);
        }

        if ($context->getOption('subject_type') === 'message') {
            $data            = $event->getData();
            $message         = trim(strip_tags(html_entity_decode(@$data['message']['message'])));
            $data['subject'] = '';
            $num             = 0;
            $delim           = " \n\t,.!?:;";
            $word            = strtok($message, $delim);
            while ($num++ < 5 && $word !== false) {
                if ($word) {
                    $data['subject'] = $data['subject'].' '.$word;
                }
                $word = strtok($delim);
            }
            $event->setData($data);
        }

        $extracted = TicketLayoutHelper::getExtractedData($event->getData() ?: [], $context);

        TicketLayoutHelper::renderFormFields($context, function (LayoutField $field) use ($extracted) {
            return $field->getCriteria()->isSubmittedDataMatch($extracted);
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
        /** @var TicketMessage $ticketMessage */
        $ticketMessage = $ticket->messages->first();
        if ($ticketMessage) {
            $person = $ticket->getPerson();
            $ticketMessage->setPerson($person);
            foreach ($ticketMessage->getAttachments() as $attachment) {
                $blob = $attachment->getBlob();
                if ($blob) {
                    $blob->is_temp = false;
                }

                $attachment->setPerson($person);
            }
        }
    }
}
