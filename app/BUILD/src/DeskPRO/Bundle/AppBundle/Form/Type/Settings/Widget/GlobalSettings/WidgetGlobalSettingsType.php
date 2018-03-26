<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\GlobalSettings;

use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetGlobalSettingsType.
 */
class WidgetGlobalSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('chat', WidgetGlobalChatSettingsType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => WidgetGlobalSettings::class,
            'allow_extra_fields' => true,
        ]);
    }
}
