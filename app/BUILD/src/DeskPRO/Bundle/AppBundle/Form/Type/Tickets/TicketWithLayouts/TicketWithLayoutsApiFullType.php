<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\ApiFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\ApiFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This is stub form. Used to output a 'full' form with every field,
 * which is used by JS to dynamically update the UI as a user changes options.
 *
 * Class TicketWithLayoutsApiFullType.
 */
class TicketWithLayoutsApiFullType extends AbstractType
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
     * @param TicketLayoutFactory $layoutFactory
     * @param ApiFieldResolver    $fieldResolver
     * @param ApiFieldRenderer    $fieldRenderer
     */
    public function __construct(TicketLayoutFactory $layoutFactory, ApiFieldResolver $fieldResolver, ApiFieldRenderer $fieldRenderer)
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_resolver' => $this->fieldResolver,
            'field_renderer' => $this->fieldRenderer,
            'full_layout'    => $this->layoutFactory->getFullLayoutForTicketForm(true),
            'layout_factory' => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, false);
            },
        ]);
    }
}
