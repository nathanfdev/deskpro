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

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedArticle;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedChat;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedTicket;
use DeskPRO\Bundle\AppBundle\Entity\TaskList;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DeskPRO\Bundle\AppBundle\Form\Type\Labels\LabelsCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\LinkedArticleType;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\LinkedChatType;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\LinkedTicketType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'description' => 'the task title',
            ])
            ->add('is_done', ApiBooleanType::class, [
                'description' => 'the task status',
                'required'    => false,
            ])
            ->add('percent_complete', IntegerType::class, [
                'required'    => false,
                'description' => 'the percentage of the task complete',
            ])
            ->add('task_type', ChoiceType::class, [
                'description' => 'the type of task',
                'required'    => false,
                'empty_data'  => Task::TYPE_TASK,
                'choices'     => [
                    Task::TYPE_TASK  => 'Task',
                    Task::TYPE_EVENT => 'Event',
                ],
            ])
            ->add('date_due', DateTimeType::class, [
                'required'    => false,
                'widget'      => 'single_text',
                'description' => 'the task due date',
            ])
            ->add('date_done', DateTimeType::class, [
                'required'    => false,
                'widget'      => 'single_text',
                'description' => 'the task done date',
            ])
            ->add('date_event_start', DateTimeType::class, [
                'required'    => false,
                'description' => 'the event start datetime',
            ])
            ->add('date_event_end', DateTimeType::class, [
                'required'    => false,
                'description' => 'the event end datetime',
            ])
            ->add('visibility', ChoiceType::class, [
                'required'    => false,
                'description' => 'the task visibility',
                'empty_data'  => Task::VISIBILITY_PRIVATE,
                'choices'     => [
                    Task::VISIBILITY_PUBLIC  => 'Public',
                    Task::VISIBILITY_PROJECT => 'Project',
                    Task::VISIBILITY_PRIVATE => 'Private',
                ],
            ])
            ->add('urgency', IntegerType::class, [
                'required'    => false,
                'empty_data'  => '5',
                'description' => 'the task urgency',
            ])
            ->add('display_order', IntegerType::class, [
                'required'    => false,
                'description' => 'the task position in a list',
            ])
            ->add('project', EntityType::class, [
                'class'    => TaskProject::class,
                'property' => 'title',
            ])
            ->add('list', EntityType::class, [
                'class'    => TaskList::class,
                'property' => 'title',
            ])
            ->add('labels', LabelsCollectionType::class, [
                'labels_class'   => LabelTask::class,
                'labels_owner'   => $builder->getData(),
                'owner_property' => 'task',
            ])
            ->add('departments', EntityType::class, [
                'class'    => Department::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('teams', EntityType::class, [
                'class'    => AgentTeam::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('agents', EntityType::class, [
                'class'    => Person::class,
                'multiple' => true,
                'required' => false,
            ])
            ->add('linked_tickets', new SetCollectionType(), [
                'entry_property_path' => 'ticket.id',
                'entry_type'          => LinkedTicketType::class,
                'entry_options'       => [
                    'empty_data' => function (FormInterface $form) use ($builder) {
                        return new TaskLinkedTicket($builder->getData());
                    },
                ],
            ])
            ->add('linked_articles', new SetCollectionType(), [
                'entry_property_path' => 'article.id',
                'entry_type'          => LinkedArticleType::class,
                'entry_options'       => [
                    'empty_data' => function (FormInterface $form) use ($builder) {
                        return new TaskLinkedArticle($builder->getData());
                    },
                ],
            ])
            ->add('linked_chats', new SetCollectionType(), [
                'entry_property_path' => 'chat.id',
                'entry_type'          => LinkedChatType::class,
                'entry_options'       => [
                    'empty_data' => function (FormInterface $form) use ($builder) {
                        return new TaskLinkedChat($builder->getData());
                    },
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
