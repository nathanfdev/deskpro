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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TaskType.
 */
class TaskType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'task';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
                'empty_data'  => Task::TYPE_TASK,
                'choices'     => [
                    Task::TYPE_TASK  => 'Task',
                    Task::TYPE_EVENT => 'Event',
                ],
            ])
            ->add('date_due', 'datetime', [
                'required'    => false,
                'widget'      => 'single_text',
                'description' => 'the task due date',
            ])
            ->add('date_done', 'datetime', [
                'required'    => false,
                'widget'      => 'single_text',
                'description' => 'the task done date',
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
                'empty_data'  => Task::VISIBILITY_PRIVATE,
                'choices'     => [
                    Task::VISIBILITY_PUBLIC  => 'Public',
                    Task::VISIBILITY_PROJECT => 'Project',
                    Task::VISIBILITY_PRIVATE => 'Private',
                ],
            ])
            ->add('urgency', 'integer', [
                'required'    => false,
                'empty_data'  => '5',
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
            ->add('departments', 'entity', [
                'class'    => 'DeskPRO:Department',
                'multiple' => true,
                'required' => false,
            ])
            ->add('teams', 'entity', [
                'class'    => 'DeskPRO:AgentTeam',
                'multiple' => true,
                'required' => false,
            ])
            ->add('agents', 'entity', [
                'class'    => 'DeskPRO:Person',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\Task',
        ]);
    }
}
