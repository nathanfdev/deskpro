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
//        ->add(
//            'departments',
//            'collection',
//            array(
//                'type' => 'department',
//                'allow_add' => true,
//                'allow_delete' => true,
//                'delete_empty' => true,
//                'options' => array(
//                    'project' => $options['project'],
//                    'required' => false,
//                    'description' => 'project members which are departments',
//                ),
//            )
//        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DeskPRO\Bundle\AppBundle\Entity\TaskProject',
            'project' => null,
            'entity_manager' => null,
        ));
    }
}
