<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Button;

use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings\WidgetBrandButtonSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandButtonSettingsType.
 */
class WidgetBrandButtonSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', CollectionType::class, [
                'entry_type'     => WidgetBrandButtonTranslationType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
            ])
            ->add('size', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'           => [
                    WidgetBrandButtonSettings::SIZE_SMALL,
                    WidgetBrandButtonSettings::SIZE_MEDIUM,
                    WidgetBrandButtonSettings::SIZE_LARGE,
                ],
            ])
            ->add('colors', WidgetBrandButtonColorsSettingsType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandButtonSettings::class,
        ]);
    }
}
