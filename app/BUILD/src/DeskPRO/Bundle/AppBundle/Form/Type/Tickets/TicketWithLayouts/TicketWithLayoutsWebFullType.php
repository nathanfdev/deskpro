<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\WebFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\WebFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
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
            'person' => function (Options $options) {
                // fake person/org to process the full form
                $person = new Person();
                $person->setOrganization(new Organization());

                return $person;
            },
            'data' => function (Options $options) {
                // fake ticket to process the full form
                $ticket = new Ticket();
                $ticket->disableAutoTicketProcess();
                $ticket->setPerson($options['person']);

                return $ticket;
            },
        ]);
    }
}
