<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketFollowUp;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketFollowUpType.
 */
class TicketFollowUpType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date_to_run', DateTimeType::class, [
                'property_path' => 'dateToRun',
                'widget'        => 'single_text',
                'required'      => true,
            ])
            ->add('actions', TicketMacroActionsType::class, [
                'required' => true,
            ])
            ->add('cancel_if_user_reply', ApiBooleanType::class, [
                'property_path' => 'cancelIfUserReply',
                'required'      => true,
            ])
        ;

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => TicketFollowUp::class,
            ])
            ->setRequired(['person', 'ticket'])
            ->setAllowedTypes('person', Person::class)
            ->setAllowedTypes('ticket', Ticket::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        /** @var Person $person */
        $person = $form->getConfig()->getOption('person');
        /** @var Ticket $ticket */
        $ticket = $form->getConfig()->getOption('ticket');

        if ($data instanceof TicketFollowUp) {
            $data->setPerson($person);
            $ticket->addFollowUp($data);
        }
    }
}
