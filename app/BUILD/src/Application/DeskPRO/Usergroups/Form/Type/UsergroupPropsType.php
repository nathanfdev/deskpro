<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usergroups\Form\Type;

use Application\DeskPRO\Entity\Usergroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsergroupPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('note', 'text', ['required' => true]);
        $builder->add('is_enabled', 'checkbox');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Usergroup::class,
            ]
        );
    }

    public function getName()
    {
        return 'usergroup';
    }
}
