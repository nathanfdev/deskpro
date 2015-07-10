<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskSubtaskType extends AbstractType
{
    public function getName()
    {
        return 'subtask';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ReplaceNotSubmittedValuesWithDefaultsListener());
        $builder->add(
                'title',
                'text',
                array(
                    'description' => 'the task title',
                    'required' => false,
                )
            )
            ->add(
                'done',
                'checkbox',
                array(
                    'description' => 'the task status',
                    'required' => false,
                )
            )
            ->add(
                'task',
                'entity',
                array(
                    'class' => 'App:Task',
                    'property' => 'title',
                    'required' => false,
                )
            )
            ->add(
                'creator',
                'entity',
                array(
                    'class' => 'DeskPRO:Person',
                    'property' => 'name',
                    'required' => false,
                )
            )
            ->add(
                'display_order',
                'integer',
                array(
                    'description' => 'the order of the subtask',
                    'required' => false,
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\TaskSubtask',
        ));
    }
}