<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandCommonSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandCommonSettingsType.
 */
class WidgetBrandCommonSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandCommonSettings::TYPE_COLUMN,
                    WidgetBrandCommonSettings::TYPE_BUBBLE,
                ],
            ])
            ->add('position', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandCommonSettings::POSITION_LEFT,
                    WidgetBrandCommonSettings::POSITION_RIGHT,
                ],
            ])
            ->add('agent_polling_timeout', IntegerType::class, [
                'property_path' => 'agentPollingTimeout',
            ])
            ->add('enabled', ApiBooleanType::class)
            ->add('primary_color', TextType::class, [
                'property_path' => 'primaryColor',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandCommonSettings::class,
        ]);
    }
}
