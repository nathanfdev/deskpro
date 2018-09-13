<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAssetAuthType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AgentDataProfileType.
 */
class AgentDataProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('available_status', ChoiceType::class, [
                'property_path'     => 'availableStatus',
                'required'          => false,
                'choices_as_values' => true,
                'choices'           => [
                    AgentData::AVAILABLE_STATUS_IDLE,
                    AgentData::AVAILABLE_STATUS_OFFLINE,
                ],
            ])
            ->add('agent_calls_enabled', ApiBooleanType::class, [
                'property_path' => 'agentCallsEnabled',
                'required'      => false,
            ])
            ->add('agent_can_use_forwarding', ApiBooleanType::class, [
                'property_path' => 'agentCanUseForwarding',
                'required'      => false,
            ])
            ->add('voicemail_asset', VoiceAssetAuthType::class, [
                'property_path' => 'voicemailAsset',
                'required'      => false,
            ])
            ->add('forwarding_number', TextType::class, [
                'property_path' => 'forwardingNumber',
                'required'      => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AgentData::class,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['available_status']) && $data['available_status'] === AgentData::AVAILABLE_STATUS_OFFLINE) {
            $data['agent_calls_enabled'] = false;
        }

        $event->setData($data);
    }
}
