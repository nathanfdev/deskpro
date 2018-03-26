<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\Agents\Type;

use Application\DeskPRO\Form\Type\PhoneNumberType;
use Application\DeskPRO\People\Agents\EditAgent;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditAgentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => true]);
        $builder->add('override_name', 'text', ['required' => false]);

        $builder->add('primary_phone', new PhoneNumberType());

        $builder->add('emails', 'collection', [
            'type'            => 'email',
            'allow_add'       => true,
            'allow_delete'    => true,
            'invalid_message' => 'Invalid Email.',
        ]);

        $builder->add('zones', 'choice', [
            'choices'  => ['admin' => 'admin', 'reports' => 'reports'],
            'multiple' => true,
            'required' => false,
        ]);

        $builder->add('teams', 'entity', [
            'class'           => 'DeskPRO:AgentTeam',
            'required'        => false,
            'multiple'        => true,
            'invalid_message' => 'Invalid Team.',
        ]);

        $builder->add('agent_groups', 'entity', [
            'class'         => 'DeskPRO:Usergroup',
            'required'      => false,
            'multiple'      => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('ug')->where('ug.is_agent_group = true');
            },
            'invalid_message' => 'Invalid Agent Group.',
        ]);

        $builder->add('primary_team', 'entity', [
            'class'           => 'DeskPRO:AgentTeam',
            'required'        => false,
            'invalid_message' => 'Invalid Agent Team.',
        ]);

        $builder->add('notification_settings', 'collection');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => EditAgent::class,
            'cascade_validation' => true,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'agent';
    }
}
