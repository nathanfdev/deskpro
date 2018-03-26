<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Chat;

use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatPopupSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandChatPopupSettingsType.
 */
class WidgetBrandChatPopupSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', CollectionType::class, [
                'entry_type'     => WidgetBrandChatPopupTranslationType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
            ])
            ->add('style', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandChatPopupSettings::STYLE_AGENT_TEXT_BUTTON,
                    WidgetBrandChatPopupSettings::STYLE_AGENT_TEXT_INPUT,
                    WidgetBrandChatPopupSettings::STYLE_AGENTS_BUTTON,
                    WidgetBrandChatPopupSettings::STYLE_TEXT_BUTTON,
                    WidgetBrandChatPopupSettings::STYLE_TEXT_INPUT,
                    WidgetBrandChatPopupSettings::STYLE_WIDGET_BUTTON_AGENT,
                ],
            ])
            ->add('delay', NumberType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandChatPopupSettings::class,
        ]);
    }
}
