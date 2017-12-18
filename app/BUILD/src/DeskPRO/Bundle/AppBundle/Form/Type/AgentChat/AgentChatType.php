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

namespace DeskPRO\Bundle\AppBundle\Form\Type\AgentChat;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentChatType.
 */
class AgentChatType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DepartmentDataService
     */
    private $departmentDataService;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param DepartmentDataService $departmentDataService
     */
    public function __construct(EntityManager $em, DepartmentDataService $departmentDataService)
    {
        $this->em                    = $em;
        $this->departmentDataService = $departmentDataService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', ChoiceType::class, [
            'required' => true,
            'choices'  => [
                AgentChat::TYPE_AGENT,
                AgentChat::TYPE_TEAM,
                AgentChat::TYPE_DEPARTMENT,
                AgentChat::TYPE_GROUP,
                AgentChat::TYPE_EVERYONE,
            ],
            'choices_as_values' => true,
            'constraints'       => [
                new Assert\NotNull(),
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onAddParticipantField']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onFindExistingChat'], 200);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSetParticipants'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['person'])
            ->setDefaults([
                'mapped'     => false,
                'data_class' => AgentChat::class,
            ])
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onAddParticipantField(FormEvent $event)
    {
        $form   = $event->getForm();
        $person = $this->getPerson($event);
        $data   = $event->getData();
        $type   = isset($data['type']) ? $data['type'] : null;

        $multiple = false;
        switch ($type) {
            case AgentChat::TYPE_GROUP:
                $form->add('name', TextType::class, [
                    'required'    => true,
                    'constraints' => [
                        new Assert\NotNull(),
                        new Assert\NotBlank(),
                    ],
                ]);
                $multiple = true;
            case AgentChat::TYPE_AGENT:
                $form->add('participant', EntityType::class, [
                    'required'    => true,
                    'mapped'      => false,
                    'multiple'    => $multiple,
                    'class'       => Person::class,
                    'constraints' => [
                        new Assert\NotNull(),
                    ],
                    'query_builder' => function (EntityRepository $er) use ($person) {
                        return $er
                            ->createQueryBuilder('u')
                            ->where('u.is_agent = 1')
                            ->andWhere('u.id != :person_id')
                            ->setParameter('person_id', $person->getId())
                        ;
                    },
                ]);

                break;
            case AgentChat::TYPE_TEAM:
                $form->add('participant', EntityType::class, [
                    'required'    => true,
                    'mapped'      => false,
                    'class'       => AgentTeam::class,
                    'choices'     => $person->getTeams(),
                    'constraints' => [
                        new Assert\NotNull(),
                    ],
                ]);

                break;
            case AgentChat::TYPE_DEPARTMENT:
                $form->add('participant', EntityType::class, [
                    'required'    => true,
                    'mapped'      => false,
                    'class'       => Department::class,
                    'choices'     => $this->departmentDataService->getTicketDepartmentsForPerson($person),
                    'constraints' => [
                        new Assert\NotNull(),
                    ],
                ]);

                break;
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onFindExistingChat(FormEvent $event)
    {
        /** @var AgentChatRepository $repository */
        $repository  = $this->em->getRepository(AgentChat::class);
        $person      = $this->getPerson($event);
        $participant = $this->getFormParticipant($event);
        $type        = $this->getFormType($event);

        if ($participant) {
            switch ($type) {
                case AgentChat::TYPE_AGENT:
                    $existChat = $repository->findChatWithAgent($person, $participant);
                    break;
                case AgentChat::TYPE_TEAM:
                    $existChat = $repository->findTeamChat($participant);
                    break;
                case AgentChat::TYPE_DEPARTMENT:
                    $existChat = $repository->findDepartmentChat($participant);
                    break;
            }
        } elseif ($type === AgentChat::TYPE_EVERYONE) {
            $existChat = $repository->findEveryoneChat();
        }

        if (isset($existChat) && $existChat instanceof AgentChat) {
            $event->setData($existChat);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSetParticipants(FormEvent $event)
    {
        /** @var AgentChat $agentChat */
        $agentChat   = $event->getData();
        $participant = $this->getFormParticipant($event);
        $person      = $this->getPerson($event);

        if ($participant &&
            (is_array($participant) || $participant instanceof \Traversable || $participant = [$participant])
        ) {
            foreach ($participant as $item) {
                if (!$agentChat->containsParticipant($item)) {
                    $agentChat->addParticipant($item);
                }
            }
            $collection = $participant instanceof ArrayCollection ? $participant : new ArrayCollection($participant);
            foreach ($agentChat->getAgents() as $agent) {
                if (!$collection->contains($agent) && $agent != $person) {
                    $agentChat->removeParticipant($agent);
                }
            }
        }

        $formType = $this->getFormType($event);
        if (($formType === AgentChat::TYPE_AGENT || $formType === AgentChat::TYPE_GROUP) && !$agentChat->getId()) {
            $agentChat->addParticipant($this->getPerson($event));
        }
        if ($formType === AgentChat::TYPE_GROUP) {
            $agentChat->setAdmin($this->getPerson($event));
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return string
     */
    private function getFormType(FormEvent $event)
    {
        return $event->getForm()->get('type')->getData();
    }

    /**
     * @param FormEvent $event
     *
     * @return mixed
     */
    private function getFormParticipant(FormEvent $event)
    {
        $form = $event->getForm();

        return $form->has('participant') ? $form->get('participant')->getData() : null;
    }

    /**
     * @param FormEvent $event
     *
     * @return Person
     */
    private function getPerson(FormEvent $event)
    {
        return $event->getForm()->getConfig()->getOption('person');
    }
}
