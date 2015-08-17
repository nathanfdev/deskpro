<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Form\DataTransformer\AgentTeamTaskAssignmentTransformer;
use DeskPRO\Bundle\AppBundle\Form\EventListener\ReplaceNotSubmittedValuesWithDefaultsListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaskAgentTeamType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getName()
    {
        return 'task_agent_team';
    }

    public function getParent()
    {
        return 'text';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new AgentTeamTaskAssignmentTransformer($this->entityManager, $options['task']));
        $builder->addViewTransformer(new AgentTeamTaskAssignmentTransformer($this->entityManager, $options['task']));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Application\DeskPRO\Entity\AgentTeam',
            'task' => null,
        ));
    }
}
