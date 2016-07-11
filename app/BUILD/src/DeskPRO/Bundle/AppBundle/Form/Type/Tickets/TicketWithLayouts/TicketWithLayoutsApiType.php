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

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\ApiFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\ApiFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketWithLayoutsApiType.
 */
class TicketWithLayoutsApiType extends AbstractType
{
    /**
     * @var TicketLayoutFactory
     */
    private $layoutFactory;

    /**
     * @var ApiFieldResolver
     */
    private $fieldResolver;

    /**
     * @var ApiFieldRenderer
     */
    private $fieldRenderer;

    /**
     * Constructor.
     *
     * @param ApiFieldResolver    $fieldResolver
     * @param ApiFieldRenderer    $fieldRenderer
     * @param TicketLayoutFactory $layoutFactory
     */
    public function __construct(ApiFieldResolver $fieldResolver, ApiFieldRenderer $fieldRenderer, TicketLayoutFactory $layoutFactory)
    {
        $this->fieldResolver = $fieldResolver;
        $this->fieldRenderer = $fieldRenderer;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onEnsureRequireFields'], 100);
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_resolver' => $this->fieldResolver,
            'field_renderer' => $this->fieldRenderer,
            'layout_factory' => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, true);
            },
        ]);
    }

    /**
     * We use partial updates for POST and PATCH request.
     * So we need to make sure that required fields are in the request for new tickets.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onEnsureRequireFields(FormEvent $event)
    {
        $ticket = $event->getForm()->getData();
        $data   = $event->getData();

        // should applied for new tickets only
        if (!$ticket instanceof Ticket || $ticket->getId()) {
            return;
        }

        if (!isset($data[FormFields::DEPARTMENT])) {
            $data[FormFields::DEPARTMENT] = '';
        }
        if (!isset($data[FormFields::SUBJECT])) {
            $data[FormFields::SUBJECT] = '';
        }
        if (!isset($data[FormFields::MESSAGE])) {
            $data[FormFields::MESSAGE] = ['message' => ''];
        }

        $event->setData($data);
    }
}
