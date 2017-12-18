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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSla;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketSlaType.
 */
class TicketSlaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sla', EntityType::class, [
                'class'    => Sla::class,
                'required' => true,
            ])
            ->add('sla_status', ChoiceType::class, [
                'required'          => true,
                'choices_as_values' => true,
                'choices'           => [
                    TicketSla::STATUS_OK,
                    TicketSla::STATUS_WARNING,
                    TicketSla::STATUS_FAIL,
                ],
            ])
        ;

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('ticket')
            ->setDefaults([
                'data_class' => TicketSla::class,
            ])
            ->setAllowedTypes('ticket', Ticket::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        if ($data instanceof TicketSla) {
            $data->setTicket($form->getConfig()->getOption('ticket'));
        }
    }
}
