<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget;

use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\WidgetBrandSettingsType;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\GlobalSettings\WidgetGlobalSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\WidgetOptions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetOptionsType.
 */
class WidgetOptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('global', WidgetGlobalSettingsType::class)
            ->add('brand', WidgetBrandSettingsType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetOptions::class,
        ]);
    }
}
