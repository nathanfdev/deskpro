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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFlagged;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketStarType.
 */
class TicketStarType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('color', ChoiceType::class, [
            'choices'           => TicketFlagged::$colorMap,
            'choices_as_values' => true,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetInlineData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onUpdateStar']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['ticket', 'person'])
            ->setDefaults([
                'data_class' => TicketFlagged::class,
                'inline'     => false,
            ])
            ->setAllowedTypes('ticket', Ticket::class)
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetInlineData(FormEvent $event)
    {
        if ($event->getForm()->getConfig()->getOption('inline')) {
            $event->setData([
                'color' => $event->getData(),
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onUpdateStar(FormEvent $event)
    {
        $options = $event->getForm()->getConfig()->getOptions();

        /** @var Ticket $ticket */
        $ticket = $options['ticket'];
        /** @var Person $person */
        $person = $options['person'];

        $data = $event->getData();
        if (!$data instanceof TicketFlagged) {
            return;
        }

        $personStar = $ticket->getPersonStar($person);
        if ($personStar) {
            if ($data->getColor()) {
                $personStar->setColor($data->getColor());
            } else {
                $ticket->getStars()->removeElement($personStar);
            }
        } elseif ($data->getColor()) {
            $data->setTicket($ticket);
            $data->setPerson($person);

            $ticket->getStars()->add($data);
        }
    }
}
