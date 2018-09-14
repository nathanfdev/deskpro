<?php

namespace DeskPRO\Bundle\ReportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class ReportWidgetVariableType.
 */
class ReportWidgetVariableType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', TextType::class, [
                'required' => true,
            ])
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('default', TextType::class, [
                'required' => false,
            ])
            ->add('value', TextType::class, [
                'required' => false,
            ])
            ->add('field_type', TextType::class, [
                'required' => false,
            ])
            ->add('field_value', TextType::class, [
                'required' => false,
            ])
            ->add('table', TextType::class, [
                'required' => false,
            ])
        ;
    }
}
