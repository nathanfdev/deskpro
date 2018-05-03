<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Settings\Widget\BrandSettings\Chat;

use DeskPRO\Bundle\AppBundle\Form\Type\Settings\BaseTranslationType;
use DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings\WidgetBrandChatPopupTranslation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetBrandChatPopupTranslationType.
 */
class WidgetBrandChatPopupTranslationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('message', TextType::class)
            ->add('heading', TextType::class)
            ->add('subheading', TextType::class)
            ->add('start_button', TextType::class, [
                'property_path' => 'startButton',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WidgetBrandChatPopupTranslation::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseTranslationType::class;
    }
}
