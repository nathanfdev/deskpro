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
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectMemberType extends AbstractType
{
    public function getName()
    {
        return 'projectmember';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'person',
                'entity',
                [
                    'class'    => Person::class,
                    'property' => 'id',
                    'required' => false,
                ]
            )
            ->add(
                'team',
                'entity',
                [
                    'class'    => AgentTeam::class,
                    'property' => 'id',
                    'required' => false,
                ]
            )
            ->add(
                'department',
                'entity',
                [
                    'class'    => Department::class,
                    'property' => 'id',
                    'required' => false,
                ]
            )
            ->add(
                'project',
                'entity',
                [
                    'class'    => TaskProject::class,
                    'property' => 'id',
                    'required' => true,
                ]
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\ProjectMember',
            ]
        );
    }
}
