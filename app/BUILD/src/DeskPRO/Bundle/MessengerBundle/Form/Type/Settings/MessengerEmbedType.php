<?php

namespace DeskPRO\Bundle\MessengerBundle\Form\Type\Settings;

use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\MessengerBundle\Settings\Model\MessengerEmbed;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MessengerEmbedType.
 */
class MessengerEmbedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('showOnPortal', ApiBooleanType::class)
            ->add('authorizeDomains', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessengerEmbed::class,
        ]);
    }
}
