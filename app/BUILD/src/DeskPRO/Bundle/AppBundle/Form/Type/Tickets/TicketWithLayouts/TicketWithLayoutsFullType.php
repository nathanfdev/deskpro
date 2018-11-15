<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketLayout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractTicketWithLayoutsFullType.
 */
class TicketWithLayoutsFullType extends AbstractType
{
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('full_layout')
            ->setAllowedTypes('full_layout', TicketLayout::class)
            ->setDefaults([
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
            ])
        ;
    }

    /**
     * Returns fields from all layouts.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        // render full layout
        $context = new TicketWithLayoutsContext($event->getForm(), $event->getData(), new TicketLayout());
        $context->setNewLayout($event->getForm()->getConfig()->getOption('full_layout'));
        $context->setFullLayout(true);

        TicketLayoutHelper::renderFormFields($context, function () {
            // just stub, no need form field validation for the 'full' form
            return true;
        });
    }
}
