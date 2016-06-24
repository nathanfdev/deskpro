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

use Application\DeskPRO\Entity\TicketLayout;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\WebFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\WebFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is stub form. Used to output a 'full' form with every field,
 * which is used by JS to dynamically update the UI as a user changes options.
 *
 * Class TicketWithLayoutsWebFullType.
 */
class TicketWithLayoutsWebFullType extends AbstractType
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
     * @param TicketLayoutFactory $layoutFactory
     * @param WebFieldResolver    $fieldResolver
     * @param WebFieldRenderer    $fieldRenderer
     */
    public function __construct(TicketLayoutFactory $layoutFactory, WebFieldResolver $fieldResolver, WebFieldRenderer $fieldRenderer)
    {
        $this->layoutFactory = $layoutFactory;
        $this->fieldResolver = $fieldResolver;
        $this->fieldRenderer = $fieldRenderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketWithLayoutsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onRenderFullLayout']);
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_resolver' => $this->fieldResolver,
            'field_renderer' => $this->fieldRenderer,
            'layout_factory' => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, false);
            },
        ]);
    }

    /**
     * Returns fields from all layouts.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onRenderFullLayout(FormEvent $event)
    {
        $context = new TicketWithLayoutsContext($event->getForm(), $event->getData(), new TicketLayout());
        $context->setNewLayout($this->layoutFactory->getFullLayoutForTicketForm());

        TicketLayoutHelper::renderFormFields($context, function () {
            // just stub, no need form field validation for the 'full' form
            return true;
        });

        $this->fieldRenderer->addSubmitButton($context);
    }
}
