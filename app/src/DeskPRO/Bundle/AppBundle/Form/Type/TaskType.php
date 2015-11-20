<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TaskType.
 */
class TaskType extends AbstractType
{
    /**
     * @var Task
     */
    private $task;

    /**
     * Get the name of the object.
     *
     * @return string
     */
    public function getName()
    {
        return 'task';
    }

    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->task = $options['task'];
        $builder
            ->add('title', 'text', [
                'description' => 'the task title',
            ])
            ->add('is_done', 'api_boolean', [
                'description' => 'the task status',
                'required'    => false,
            ])
            ->add('percent_complete', 'integer', [
                'required'    => false,
                'description' => 'the percentage of the task complete',
            ])
            ->add('task_type', 'choice', [
                'description' => 'the type of task',
                'required'    => false,
                'choices'     => [
                    'task'  => 'Task',
                    'event' => 'Event',
                ],
            ])
            ->add('date_due', 'datetime', [
                'required'    => false,
                'widget'      => 'single_text',
                'description' => 'the task due date',
            ])
            ->add('date_event_start', 'datetime', [
                'required'    => false,
                'description' => 'the event start datetime',
            ])
            ->add('date_event_end', 'datetime', [
                'required'    => false,
                'description' => 'the event end datetime',
            ])
            ->add('visibility', 'choice', [
                'required'    => false,
                'description' => 'the task visibility',
                'choices'     => [
                    'public'  => 'Public',
                    'project' => 'Project',
                    'private' => 'Private',
                ],
            ])
            ->add('urgency', 'integer', [
                'required'    => false,
                'description' => 'the task urgency',
            ])
            ->add('display_order', 'integer', [
                'required'    => false,
                'description' => 'the task position in a list',
            ])
            ->add('project', 'entity', [
                'class'    => 'App:TaskProject',
                'property' => 'title',
            ])
            ->add('list', 'entity', [
                'class'    => 'App:TaskList',
                'property' => 'title',
            ])
            ->add('labels', 'api_labels_collection', [
                'labels_class'   => LabelTask::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'task',
            ])
            ->add('departments', 'collection',  [
                'type'         => 'task_department',
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'options'      => [
                    'task'        => $options['task'],
                    'required'    => false,
                    'description' => 'task assignees which are departments',
                ],
            ])
            ->add('teams', 'collection', [
                'type'         => 'task_agent_team',
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'options'      => [
                    'task'        => $options['task'],
                    'required'    => false,
                    'description' => 'task assignees which are teams',
                ],
            ])
            ->add('agents', 'collection', [
                'type'         => 'task_person',
                'allow_add'    => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'options'      => [
                    'task'        => $options['task'],
                    'required'    => false,
                    'description' => 'task assignees which are people',
                ],
            ])
            ->addEventSubscriber(new ReplaceNotSubmittedValuesWithDefaultsListener())
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * The the default options for the form.
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'     => 'DeskPRO\Bundle\AppBundle\Entity\Task',
            'task'           => null,
            'entity_manager' => null,
        ]);
    }

    /**
     * Code to be executed when the form is submitted
     * This removes any labels which were not submitted by the form.
     *
     * @param FormEvent $event The submit event
     */
    public function onSubmit(FormEvent $event)
    {
        /** @var Task $data */
        $data       = $event->getData();
        $newMembers = $data->getLabels();

        foreach ($this->task->getLabels() as $label) {
            if (!$newMembers->contains($label)) {
                $this->task->removeLabel($label);
            }
        }
    }
}
