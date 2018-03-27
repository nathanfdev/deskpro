<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceAutoAttendantType.
 */
class VoiceAutoAttendantType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('audio_asset', VoiceAssetAuthType::class, [
                'property_path' => 'audioAsset',
                'required'      => false,
            ])
            ->add('targets', VoiceAutoAttendantDialNumberCollectionType::class, [
                'auto_attendant' => $builder->getData(),
            ])
            ->add('allow_repeat_menu', ApiBooleanType::class, [
                'property_path' => 'allowRepeatMenu',
            ])
            ->add('allow_extension', ApiBooleanType::class, [
                'property_path' => 'allowExtension',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoiceAutoAttendant::class,
        ]);
    }
}
