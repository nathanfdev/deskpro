<?php

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
