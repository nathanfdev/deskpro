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

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Tasks;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionParamsType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as CoreDateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TaskMassActionParamsType.
 */
class TaskMassActionParamsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('set_project', EntityType::class, [
                'class'         => TaskProject::class,
                'property_path' => 'project',
            ])
            ->add('set_status', ApiBooleanType::class, [
                'property_path' => 'is_done',
            ])
            ->add('set_due_date', CoreDateTimeType::class, [
                'property_path' => 'date_due',
                'widget'        => 'single_text',
            ])
            ->add('assign', CombinedType::class, [
                'error_bubbling' => false,
                'forms'          => [
                    [
                        'name'    => 'agents',
                        'type'    => EntityType::class,
                        'options' => [
                            'class'    => Person::class,
                            'multiple' => true,
                        ],
                    ],
                    [
                        'name'    => 'teams',
                        'type'    => EntityType::class,
                        'options' => [
                            'class'    => AgentTeam::class,
                            'multiple' => true,
                        ],
                    ],
                    [
                        'name'    => 'departments',
                        'type'    => EntityType::class,
                        'options' => [
                            'class'    => Department::class,
                            'multiple' => true,
                        ],
                    ],
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionParamsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'actions'    => ['delete'],
            'data_class' => Task::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof Task) {
            return;
        }

        if (BaseMassActionParamsType::hasAction($event, 'delete')) {
            $data->setForDel(true);
        }
    }
}
