<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewOrganization extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => false]);

        $builder->add('labels', 'collection', [
            'type'         => 'text',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);

        $builder->add('usergroup_ids', 'collection', [
            'type'         => 'text',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class'      => 'Application\\AgentBundle\\Form\\Model\\NewOrganization',
            'csrf_protection' => false,
        ];
    }

    public function getName()
    {
        return 'neworg';
    }
}
