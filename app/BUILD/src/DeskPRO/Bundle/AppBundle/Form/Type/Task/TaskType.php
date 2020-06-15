<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Task;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\LabelTask;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\TaskAssociatedTicket;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TaskType.
 */
class TaskType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required'    => true,
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\NotBlank(),
                ],
            ])
            ->add('person', EntityType::class, [
                'class'         => Person::class,
                'required'      => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where('p.is_agent = true AND p.is_deleted = false');
                },
            ])
            // UTC!
            ->add('date_due', DateTimeType::class, [
                'widget'   => 'single_text',
                'required' => false,
            ])
            ->add('visibility', ChoiceType::class, [
                'required'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    Task::PRIVATE_VISIBILITY,
                    Task::PUBLIC_VISIBILITY,
                ],
                'empty_data'  => null,
                'empty_value' => Task::PUBLIC_VISIBILITY,
            ])
            ->add('assigned_agent', EntityType::class, [
                'class'         => Person::class,
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where('p.is_agent = true AND p.is_deleted = false');
                },
            ])
            ->add('assigned_agent_team', EntityType::class, [
                'class'    => AgentTeam::class,
                'required' => false,
                'property' => 'name',
            ])
            ->add('assigned_department', EntityType::class, [
                'class'         => Department::class,
                'required'      => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')->where('p.is_tickets_enabled = true');
                },
            ])
            ->add('ticket', EntityType::class, [
                'class'    => Ticket::class,
                'required' => false,
                'mapped'   => false,
            ])
            ->add('tickets', EntityType::class, [
                'class'    => Ticket::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => LabelTask::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'task',
                'required'       => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * Parse assigned target type.
     *
     * @param FormEvent $event
     *
     * @internal
     */
    public function onPreSubmit(FormEvent $event)
    {
        $options = $event->getForm()->getConfig()->getOptions();
        $data    = $event->getData();

        // set assigned agent or team
        if (isset($data['assigned_agent']) && strpos($data['assigned_agent'], ':') !== false) {
            list($type, $id) = explode(':', $data['assigned_agent']);

            $data['assigned_agent']      = null;
            $data['assigned_agent_team'] = null;

            if ($type === 'agent') {
                $data['assigned_agent'] = $id;
            } elseif ($type === 'agent_team') {
                $data['assigned_agent_team'] = $id;
            }
        } elseif (!empty($options['person'])) {
            $data['assigned_agent'] = $options['person']->getId();
        }

        // set task owner
        if ((!isset($data['person']) || !$data['person']) && !empty($options['person'])) {
            $data['person'] = $options['person']->getId();
        }

        if (isset($data['visibility']) && strpos($data['visibility'], ':') !== false) {
            list($type, $id) = explode(':', $data['visibility']);

            $data['assigned_department'] = null;
            $data['visibility']          = Task::PRIVATE_VISIBILITY;

            if ($type === 'department') {
                $data['assigned_department'] = $id;
            }
        }

        $event->setData($data);
    }

    /**
     * Additional associations.
     *
     * @param FormEvent $event
     *
     * @internal
     */
    public function onPostSubmit(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $event->getForm()->getConfig();

        /** @var Task $task */
        $task = $form->getData();

        if ($ticket = $form->get('ticket')->getData()) {
            $assoc = new TaskAssociatedTicket();
            $assoc->setTicket($ticket);
            $assoc->setTask($task);

            $task->addTaskAssociation($assoc);
        }

        if ($tickets = $form->get('tickets')->getData()) {
            foreach ($tickets as $ticket) {
                $assoc = new TaskAssociatedTicket();
                $assoc->setTicket($ticket);
                $assoc->setTask($task);

                $task->addTaskAssociation($assoc);
            }
        }

        // set task creator
        if (!$task->getId()) {
            $task->setPerson($config->getOption('person'));
        }

        // assign to myself by default
        if (!$task->getAssignedAgent() && !$task->getAssignedDepartment() && !$task->getAssignedAgentTeam()) {
            $task->setAssignedAgent($config->getOption('person'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Task::class,
                'timezone'   => null,
                'person'     => null,
            ])
            ->setAllowedTypes('person', ['null', Person::class]);
    }
}
