<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\DateFormatter\IntlDateFormatter;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskType extends AbstractType
{
    public function getName()
    {
        return 'task';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ReplaceNotSubmittedValuesWithDefaultsListener());
        $builder->add(
                'title',
                'text',
                array(
                    'description' => 'the task title',
                )
            )
            ->add(
                'is_done',
                'api_boolean',
                array(
                    'description' => 'the task status',
                    'required' => false,
                )
            )
            ->add(
                'percent_complete',
                'integer',
                array(
                    'required' => false,
                    'description' => 'the percentage of the task complete',
                )
            )
            ->add(
                'task_type',
                'choice',
                array(
                    'description' => 'the type of task',
                    'required' => false,
                    'choices' => array('task' => 'Task', 'event' => 'Event'),
                )
            )
            ->add(
                'date_due',
                'api_date',
                array(
                    'required' => false,
                    'description' => 'the task due date',
                )
            )
            ->add(
                'date_event_start',
                'datetime',
                array(
                    'required' => false,
                    'description' => 'the event start datetime',
                )
            )
            ->add(
                'date_event_end',
                'datetime',
                array(
                    'required' => false,
                    'description' => 'the event end datetime',
                )
            )
            ->add(
                'visibility',
                'choice',
                array(
                    'required' => false,
                    'description' => 'the task visibility',
                    'choices' => array('public' => 'Public', 'project' => 'Project', 'private' => 'Private'),
                )
            )
            ->add(
                'urgency',
                'integer',
                array(
                    'required' => false,
                    'description' => 'the task urgency',
                )
            )
            ->add(
                'project',
                'entity',
                array(
                    'class' => 'App:TaskProject',
                    'property' => 'title',
                )
            )
            ->add(
                'labels',
                'collection',
                array(
                    'type' => 'label_task',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'options' => array(
                        'task' => $options['task'],
                        'required' => false,
                        'description' => 'the task labels',
                    ),
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\Task',
            'task' => null,
            'entity_manager' => null,
        ));
    }
}