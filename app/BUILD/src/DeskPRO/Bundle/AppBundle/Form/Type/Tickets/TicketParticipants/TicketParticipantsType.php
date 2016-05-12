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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayOfStringsTransformer;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Accepts comma separated list or array of emails.
 */
class TicketParticipantsType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new TicketParticipantsTransformer($this->em, $options['owner']));
        $builder->addViewTransformer(new ArrayOfStringsTransformer());

        if ($options['view_type'] === 'inline') {
            $builder->addViewTransformer(new ArrayToStringTransformer());
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onTransformIdToEmail']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onMergeData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onValidateData']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'view_type'      => 'inline',
                'compound'       => false,
                'empty_data'     => null,
                'error_bubbling' => false,
                'mapped'         => false,
            ])
            ->setRequired(['is_agent', 'owner'])
            ->setAllowedValues([
                'view_type' => ['inline', 'array'],
            ])
            ->setAllowedTypes([
                'owner'    => Ticket::class,
                'is_agent' => 'boolean',
            ])
        ;
    }

    /**
     * @param FormEvent $event
     *
     * @return array
     */
    public function onTransformIdToEmail(FormEvent $event)
    {
        $data   = $event->getData();
        $result = [];

        if (!is_array($data)) {
            return;
        }

        foreach ($data as $item) {
            if (is_numeric($item)) {
                $person   = $this->em->getRepository(Person::class)->find($item);
                $result[] = $person ? $person->getPrimaryEmailAddress() : '';
            } else {
                $result[] = $item;
            }
        }

        $event->setData($result);
    }

    /**
     * Filter participants by required person type.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $allParticipants  = $this->getAllParticipants($event);
        $formParticipants = $allParticipants->filter(function (TicketParticipant $participant) use ($event) {
            return $participant->getPerson()->isAgent() === $this->isAgent($event);
        });

        $event->setData($formParticipants);
    }

    /**
     * Merge changes to full participant collection.
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
     */
    public function onValidateData(FormEvent $event)
    {
        $form    = $event->getForm();
        $isAgent = $this->isAgent($event);

        /** @var TicketParticipant $participant */
        foreach ($this->getFormParticipants($event) as $participant) {
            $person = $participant->getPerson();
            if (!$person) {
                $errorCode = ErrorsCodes::NO_PERSON;
            } elseif ($isAgent && !$person->isAgent()) {
                $errorCode = ErrorsCodes::NOT_AGENT;
            } elseif (!$isAgent && $person->isAgent()) {
                $errorCode = ErrorsCodes::NOT_USER;
            } else {
                $errorCode = null;
            }

            if ($errorCode) {
                $form->addError(new FormError($errorCode, null, ['value' => $participant->getEmailAddress()]));
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
        $form   = $event->getForm();
        $config = $form->getConfig();

        $propertyPath = $config->getOption('property_path') ?: $form->getName();
        $participants = PropertyAccess::createPropertyAccessor()->getValue($this->getTicket($event), $propertyPath);

        return $participants ?: new ArrayCollection();
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
