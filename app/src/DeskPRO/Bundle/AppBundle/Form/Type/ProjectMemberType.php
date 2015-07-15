<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
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
        $builder->addEventSubscriber(new ReplaceNotSubmittedValuesWithDefaultsListener());
        $builder->add(
               'person',
                'entity',
                array(
                    'class' => 'DeskPRO:Person',
                    'property' => 'name',
                    'required' => false,
                )
            )
            ->add(
                'team',
                'entity',
                array(
                    'class' => 'DeskPRO:AgentTeam',
                    'property' => 'name',
                    'required' => false,
                )
            )
            ->add(
                'department',
                'entity',
                array(
                    'class' => 'DeskPRO:Department',
                    'property' => 'name',
                    'required' => false,
                )
            )
            ->add(
                'project',
                'entity',
                array(
                    'class' => 'App:TaskProject',
                    'property' => 'title',
                    'required' => true,
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\ProjectMember',
        ));
    }
}