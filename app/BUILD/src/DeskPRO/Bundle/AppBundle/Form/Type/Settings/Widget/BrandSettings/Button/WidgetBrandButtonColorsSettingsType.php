<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Button;

use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonColorsSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetButtonColorsSetupType.
 */
class WidgetBrandButtonColorsSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('background', TextType::class)
            ->add('text', TextType::class)
            ->add('border', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandButtonColorsSettings::class,
        ]);
    }
}
