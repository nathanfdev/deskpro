<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DataJsonFieldType.
 */
class DataJsonFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('usersource_id', 'text', ['required' => false]);
        $builder->add('field_name', 'text', ['required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CustomFieldTypeAbstract::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Model\DataJsonField::class,
        ]);
    }
}
