<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessengerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('embed', MessengerEmbedType::class)
            ->add('chat', MessengerChatType::class, ['brand' => $options['brand']])
            ->add('tickets', MessengerTicketsType::class, ['brand' => $options['brand']])
            ->add('proactive', MessengerProactiveType::class)
            ->add('widget', MessengerWidgetType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['data_class' => MessengerSettings::class])
            ->setRequired(['brand'])
            ->setAllowedTypes('brand', [Brand::class])
        ;
    }
}
