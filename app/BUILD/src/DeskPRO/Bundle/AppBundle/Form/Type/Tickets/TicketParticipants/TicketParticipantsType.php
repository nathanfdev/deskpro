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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayOfStringsTransformer;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use DeskPRO\Bundle\AppBundle\Form\Error\ApiErrors;
use Doctrine\Common\Collections\ArrayCollection;
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_participants';
    }

    /**
     * Filter participants by required person type.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $is_agent = $this->isAgent($event);

        $all_participants  = $this->getAllParticipants($event);
        $type_participants = $all_participants->filter(function (TicketParticipant $participant) use ($is_agent) {
            return $participant->getPerson()->isAgent() === $is_agent;
        });

        $event->setData($type_participants->toArray());
    }

    /**
     * Merge changes to full participant collection.
     *
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $is_agent = $this->isAgent($event);

        $all_participants  = $this->getAllParticipants($event);
        $type_participants = new ArrayCollection($event->getForm()->getData() ?: []);

        /** @var TicketParticipant $participant */
        foreach ($type_participants as $participant) {
            if (!$all_participants->contains($participant)) {
                $all_participants->add($participant);
            }
        }
        foreach ($all_participants as $participant) {
            $person = $participant->getPerson();
            if ($person && $person->isAgent() === $is_agent && !$type_participants->contains($participant)) {
                $all_participants->removeElement($participant);
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onValidateData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $form->getData();

        $is_agent          = $this->isAgent($event);
        $type_participants = new ArrayCollection($data ?: []);

        /** @var TicketParticipant $participant */
        foreach ($type_participants as $participant) {
            $error_code = null;
            $person     = $participant->getPerson();

            if (!$person) {
                $error_code = ApiErrors::NO_PERSON;
            } elseif ($is_agent && !$person->isAgent()) {
                $error_code = ApiErrors::NOT_AGENT;
            } elseif (!$is_agent && $person->isAgent()) {
                $error_code = ApiErrors::NOT_USER;
            }

            if ($error_code) {
                $form->addError(new FormError($error_code, null, ['value' => $participant->getEmailAddress()]));
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

        $property_path = $config->getOption('property_path') ?: $form->getName();
        $owner         = $config->getOption('owner');

        $participants = PropertyAccess::createPropertyAccessor()->getValue($owner, $property_path);

        return $participants ?: new ArrayCollection();
    }

    /**
     * @param FormEvent $event
     *
     * @return bool
     */
    protected function isAgent(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        return $config->getOption('is_agent');
    }
}
