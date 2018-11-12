<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\WebFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\WebFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
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
        return TicketWithLayoutsFullType::class;
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
            'full_layout'    => $this->layoutFactory->getFullLayoutForTicketForm(),
            'layout_factory' => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, false);
            },
        ]);
    }
}
