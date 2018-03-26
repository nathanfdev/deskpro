<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\GlobalSettings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\GlobalSettings\WidgetGlobalChatSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetGlobalChatSettingsType.
 */
class WidgetGlobalChatSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('enabled', ApiBooleanType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetGlobalChatSettings::class,
        ]);
    }
}
