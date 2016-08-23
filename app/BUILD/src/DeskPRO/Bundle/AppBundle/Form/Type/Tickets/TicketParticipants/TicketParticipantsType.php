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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
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
class TicketParticipantsType extends AbstractType
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
                        'error_bubbling' => $options['inline'],
                        'is_agent'       => $options['is_agent'],
                        'owner'          => $options['owner'],
                        'constraints'    => new Assert\Valid(),
                        'inline'         => true,
                        'set_owner'      => false,
                    ];
                },
            ])
            ->setRequired(['is_agent', 'owner'])
            ->setAllowedTypes('owner', Ticket::class)
            ->setAllowedTypes('is_agent', 'boolean')
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
        $allParticipants  = $this->getAllParticipants($event);
        $formParticipants = $allParticipants->filter(function (TicketParticipant $participant) use ($event) {
            return $participant->getPerson() && $participant->getPerson()->isAgent() === $this->isAgent($event);
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
            if ($person && $person->isAgent() === $this->isAgent($event) && !$formParticipants->contains($participant)) {
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
     * @return bool
     */
    protected function isAgent(FormEvent $event)
    {
        return $event->getForm()->getConfig()->getOption('is_agent');
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
