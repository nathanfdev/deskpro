<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AgentProfileType.
 */
class AgentProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('agent_data', AgentDataProfileType::class, [
            'property_path' => 'agentData',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Person::class,
            'constraints' => [
                new AppAssert\Person\PersonType([
                    'type' => 'agent',
                ]),
            ],
        ]);
    }
}
