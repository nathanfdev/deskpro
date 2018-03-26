<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceNumberType.
 */
class VoiceNumberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account', EntityType::class, [
                'class'    => VoiceAccount::class,
                'required' => true,
            ])
            ->add('sid', TextType::class, [
                'required' => true,
            ])
            ->add('nickname', TextType::class, [
                'required' => false,
            ])
            ->add('number', TextType::class, [
                'required' => true,
            ])
            ->add('country_code', TextType::class, [
                'property_path' => 'countryCode',
                'required'      => true,
            ])
            ->add('target', VoiceTargetType::class, [
                'error_bubbling' => false,
                'required'       => true,
            ])
            ->add('outbound_calls_enabled', ApiBooleanType::class, [
                'property_path' => 'outboundCallsEnabled',
                'required'      => false,
            ])
        ;

        $builder->get('country_code')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onLowerCountryCode']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VoiceNumber::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onLowerCountryCode(FormEvent $event)
    {
        $data = $event->getData();
        if (is_string($data)) {
            $event->setData(strtolower($data));
        }
    }
}
