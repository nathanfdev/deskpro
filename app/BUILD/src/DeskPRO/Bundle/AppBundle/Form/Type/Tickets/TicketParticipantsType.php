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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayOfStringsTransformer;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\ArrayToStringTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
        $builder->addViewTransformer(new TicketParticipantTransformer($this->em, $options['owner']));
        $builder->addViewTransformer(new ArrayOfStringsTransformer());

        if ($options['view_type'] === 'inline') {
            $builder->addViewTransformer(new ArrayToStringTransformer());
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onMergeData']);
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
                'error_bubbling' => false,
            ])
            ->setRequired(['person_type', 'owner'])
            ->setAllowedValues([
                'view_type'   => ['inline', 'array'],
                'person_type' => ['user', 'agent'],
            ])
            ->setAllowedTypes([
                'owner' => Ticket::class,
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
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form     = $event->getForm();
        $config   = $form->getConfig();
        $is_agent = $config->getOption('person_type') === 'agent';

        $all_participants  = $event->getData() ?: new ArrayCollection();
        $type_participants = $all_participants->filter(function (TicketParticipant $participant) use ($is_agent) {
            return $participant->getPerson()->isAgent() === $is_agent;
        });

        $event->setData($type_participants);
    }

    /**
     * @param FormEvent $event
     */
    public function onMergeData(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        $owner         = $config->getOption('owner');
        $property_path = $config->getOption('property_path') ?: $form->getName();
        $is_agent      = $config->getOption('person_type') === 'agent';

        $all_participants  = PropertyAccess::createPropertyAccessor()->getValue($owner, $property_path);
        $type_participants = new ArrayCollection($event->getData());

        /** @var TicketParticipant $participant */
        foreach ($type_participants as $participant) {
            if (!$all_participants->contains($participant)) {
                $all_participants->add($participant);
            }
        }
        foreach ($all_participants as $participant) {
            if ($participant->getPerson()->isAgent() === $is_agent && !$type_participants->contains($participant)) {
                $all_participants->removeElement($participant);
            }
        }

        $event->setData($all_participants);
    }
}
