<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Orb\Data\Countries;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
            ->add('outbound_calls_default', ApiBooleanType::class, [
                'property_path' => 'outboundCallsDefault',
                'required'      => false,
            ])
            ->add('outbound_calls_default_type', ChoiceType::class, [
                'property_path'     => 'outboundCallsDefaultType',
                'required'          => false,
                'multiple'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    VoiceNumber::OUTBOUND_CALLS_DEFAULT_TYPE_COUNTRY,
                    VoiceNumber::OUTBOUND_CALLS_DEFAULT_TYPE_SPECIFIC,
                    VoiceNumber::OUTBOUND_CALLS_DEFAULT_TYPE_ALL,
                ],
            ])
            ->add('outbound_calls_default_countries', ChoiceType::class, [
                'property_path'     => 'outboundCallsDefaultCountries',
                'required'          => false,
                'multiple'          => true,
                'choices_as_values' => true,
                'choices'           => array_map('strtolower', Countries::getCountryCodes()),
            ])
        ;

        $builder->get('country_code')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onCountryCodePreSubmit']);
        $builder->get('outbound_calls_default_countries')->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onOutboundCountryCodesPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
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
    public function onCountryCodePreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (is_string($data)) {
            $event->setData(strtolower($data));
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onOutboundCountryCodesPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (is_string($data)) {
            $data = [$data];
        }
        if (is_array($data)) {
            foreach ($data as &$item) {
                if (is_string($item)) {
                    $item = strtolower($item);
                }
            }
        }

        $event->setData($data);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof VoiceNumber) {
            return;
        }

        if (is_array($data->getOutboundCallsDefaultCountries())) {
            $data->setOutboundCallsDefaultCountries(array_values($data->getOutboundCallsDefaultCountries()));
        }
    }
}
