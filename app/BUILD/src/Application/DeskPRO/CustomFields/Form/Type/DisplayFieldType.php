<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\CustomFields\Form\Model\DisplayField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DisplayFieldType.
 */
class DisplayFieldType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('html', 'textarea', ['required' => true]);
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
            'data_class' => DisplayField::class,
        ]);
    }
}
