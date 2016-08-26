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
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDisableAutoProcessListener;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketParticipantType.
 */
class TicketParticipantType extends AbstractTicketParticipantType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $personConstraints = [];
        if (!$this->isAllParticipantsEnabled($options)) {
            $personConstraints[] = new AppAssert\Person\PersonType([
                'type' => $options['is_agent'] ? 'agent' : 'user',
            ]);
        }

        $builder->add('person', PersonAssignType::class, [
            'error_bubbling' => $options['inline'],
            'constraints'    => $personConstraints,
        ]);

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations'], 100);

        if ($options['inline']) {
            $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onSetInlineEmailField']);
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetInlineData']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'inline'         => false,
                'set_owner'      => true,
                'data_class'     => TicketParticipant::class,
                'error_bubbling' => false,
                'error_mapping'  => function (Options $options) {
                    return $options['inline'] ? [] : ['.' => 'person'];
                },
            ])
            ->setRequired(['is_agent', 'owner'])
            ->setAllowedTypes('is_agent', 'boolean')
            ->setAllowedTypes('inline', 'boolean')
            ->setAllowedTypes('set_owner', 'boolean')
            ->setAllowedTypes('owner', Ticket::class)
        ;
    }

    /**
     * Add person email field to keep and validate email address.
     * Uses to transform data to single input and display errors properly.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetInlineEmailField(FormEvent $event)
    {
        $data  = $event->getData();
        $email = null;

        if ($data instanceof TicketParticipant) {
            $person = $data->getPerson();
            if ($person && $person->getPrimaryEmail()) {
                $email = $person->getPrimaryEmail()->getEmail();
            } elseif ($data->getPersonEmail()) {
                $email = $data->getPersonEmail()->getEmail();
            }
        }

        $event->getForm()->add('person_email', TextType::class, [
            'data'           => $email,
            'mapped'         => false,
            'error_bubbling' => true,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetInlineData(FormEvent $event)
    {
        $data = $event->getData();

        if (is_scalar($data)) {
            $event->setData([
                'person'       => $data,
                'person_email' => $data,
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $options = $form->getConfig()->getOptions();

        if ($data instanceof TicketParticipant) {
            if (!$data->getId() && $options['set_owner']) {
                /* @var Ticket $options['owner'] */
                $options['owner']->addParticipant($data);
            }
            if ($data->getPerson()) {
                $data->setPersonEmail($data->getPerson()->getPrimaryEmail());
            }
        }
    }
}
