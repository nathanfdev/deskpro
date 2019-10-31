<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Type;

use Application\AgentBundle\Form\Model\NewPerson as NewPersonModel;
use Application\DeskPRO\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewPerson extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', ['required' => false]);
        $builder->add('email', 'text', ['required' => false]);

        $builder->add('organization_id', 'text', ['required' => false]);
        $builder->add('organization_position', 'text', ['required' => false]);

        $builder->add('new_organization', 'text', ['required' => false]);

        $builder->add('timezone', 'text', ['required' => false]);

        $builder->add('labels', 'collection', [
            'type'         => 'text',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
        $builder->add('usergroup_ids', 'collection', [
            'type'         => 'integer',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
        $builder->add('brand_ids', 'collection', [
            'type'         => 'integer',
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
        $builder->add('phone_numbers', 'collection', [
            'type'         => new PhoneNumberType(),
            'required'     => false,
            'allow_add'    => true,
            'allow_delete' => true,
        ]);
    }

    public function getDefaultOptions(array $options)
    {
        return [
            'data_class' => NewPersonModel::class,
        ];
    }

    public function getName()
    {
        return 'newperson';
    }
}
