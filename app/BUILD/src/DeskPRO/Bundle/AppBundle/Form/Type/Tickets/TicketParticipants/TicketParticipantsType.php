<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Accepts array of emails.
 */
class TicketParticipantsType extends AbstractTicketParticipantType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'], 100);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onMergeData']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'inline'         => false,
                'error_bubbling' => false,
                'mapped'         => false,
                'allow_add'      => true,
                'allow_delete'   => true,
                'entry_type'     => TicketParticipantType::class,
                'entry_options'  => function (Options $options) {
                    return [
                        'error_bubbling'  => $options['inline'],
                        'owner'           => $options['owner'],
                        'constraints'     => new Assert\Valid(),
                        'inline'          => true,
                        'set_owner'       => false,
                        'agent_interface' => $options['agent_interface'],
                    ];
                },
            ])
        ;
    }

    /**
     * Filter participants by required person type.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $isAllEnabled     = $event->getForm()->getConfig()->getOption('allow_all');
        $allParticipants  = $this->getAllParticipants($event);
        $formParticipants = $allParticipants->filter(function (TicketParticipant $participant) use ($event, $isAllEnabled) {
            return $participant->getPerson() && ($isAllEnabled || !$participant->getPerson()->isAgent());
        });

        $event->setData($formParticipants);
    }

    /**
     * Merge changes to full participant collection.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $isAllEnabled     = $event->getForm()->getConfig()->getOption('allow_all');
        $allParticipants  = $this->getAllParticipants($event);
        $formParticipants = $this->getFormParticipants($event);

        $ticket = $this->getTicket($event);

        /** @var TicketParticipant $participant */
        foreach ($formParticipants as $participant) {
            if (!$allParticipants->contains($participant)) {
                $ticket->addParticipant($participant);
            }
        }
        foreach ($allParticipants as $participant) {
            $person = $participant->getPerson();
            if (!$person) {
                continue;
            }
            if (($isAllEnabled || !$person->isAgent()) && !$formParticipants->contains($participant)) {
                $ticket->removeParticipant($participant);
            }
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return ArrayCollection
     */
    protected function getAllParticipants(FormEvent $event)
    {
        return $this->getTicket($event)->getParticipants();
    }

    /**
     * @param FormEvent $event
     *
     * @return Ticket
     */
    protected function getTicket(FormEvent $event)
    {
        return $event->getForm()->getConfig()->getOption('owner');
    }

    /**
     * @param FormEvent $event
     *
     * @return ArrayCollection
     */
    protected function getFormParticipants(FormEvent $event)
    {
        $data = $event->getForm()->getData();
        if ($data instanceof Collection) {
            return $data;
        } elseif (is_array($data)) {
            return new ArrayCollection($data);
        }

        return new ArrayCollection();
    }
}
