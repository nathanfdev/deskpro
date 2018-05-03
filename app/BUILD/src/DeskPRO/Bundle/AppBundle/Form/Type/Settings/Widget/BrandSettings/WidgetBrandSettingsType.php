<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings;

use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Button\WidgetBrandButtonSettingsType;
use DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Chat\WidgetBrandChatSettingsType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\WidgetBrandSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandSettingsType.
 */
class WidgetBrandSettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('widget', WidgetBrandCommonSettingsType::class)
            ->add('button', WidgetBrandButtonSettingsType::class)
            ->add('chat', WidgetBrandChatSettingsType::class)
            ->add('ticket', WidgetBrandTicketSettingsType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandSettings::class,
        ]);
    }
}
