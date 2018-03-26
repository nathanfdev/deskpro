<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\WidgetSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetSettingsType.
 */
class WidgetSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('settings', WidgetOptionsType::class)
            ->add('jwt_settings', JwtSettingsType::class, [
                'property_path' => 'jwtSettings',
            ])
            ->add('enabled_on_portal', ApiBooleanType::class, [
                'property_path' => 'enabledOnPortal',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetSettings::class,
        ]);
    }
}
