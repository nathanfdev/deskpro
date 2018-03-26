<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonStartType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name')
            ->add('last_name')
            ->add('email', 'email')
            ->add(
                'password',
                'repeated',
                [
                    'type'            => 'password',
                    'required'        => true,
                    'invalid_message' => 'The password fields must match.',
                    'first_options'   => ['label' => 'Password'],
                    'second_options'  => ['label' => 'Repeat Password'],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'person_start';
    }
}
