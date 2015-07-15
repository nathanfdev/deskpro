<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProjectType extends AbstractType
{
    public function getName()
    {
        return 'project';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new ReplaceNotSubmittedValuesWithDefaultsListener());
        $builder->add(
            'title',
            'text',
            array(
                'description' => 'the project title',
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\TaskProject',
        ));
    }
}