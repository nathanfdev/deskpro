<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\PersonTaskAssignmentTransformer;
use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskPersonType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getName()
    {
        return 'task_person';
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new PersonTaskAssignmentTransformer($this->entityManager, $options['task']));
        $builder->addViewTransformer(new PersonTaskAssignmentTransformer($this->entityManager, $options['task']));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\DeskPRO\Entity\Person',
            'task' => null,
        ));
    }
}
