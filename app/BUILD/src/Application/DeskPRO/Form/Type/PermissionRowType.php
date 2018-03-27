<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PermissionRowType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', [
            'required' => false,
        ]);
        $builder->add('usergroup_id', 'integer', [
            'required' => false,
        ]);
        $builder->add('person_id', 'integer', [
            'required' => false,
        ]);
        $builder->add('value', 'integer', [
            'required' => false,
            'data'     => '1',
        ]);
    }

    public function getName()
    {
        return 'permission';
    }
}
