<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CombinedType.
 */
class CombinedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['forms'] as $form) {
            $builder->add($form['name'], $form['type'], $form['options']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('forms')
            ->setAllowedTypes('forms', 'array') // an array of form types with their option sets
            ->setDefaults([
                'mapped'       => false,
                'inherit_data' => true,
            ])
        ;
    }
}
