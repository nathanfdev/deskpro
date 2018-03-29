<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usergroups\Form\Type;

use Application\DeskPRO\Usergroups\UsergroupEdit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsergroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('group', new UsergroupPropsType());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => UsergroupEdit::class,
                'cascade_validation' => true,
            ]
        );
    }

    public function getName()
    {
        return 'usergroup_edit';
    }
}
