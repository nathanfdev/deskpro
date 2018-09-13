<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAssetAuthType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PersonAgentDataType.
 */
class PersonAgentDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('extension_number', NumberType::class, [
                'property_path' => 'extensionNumber',
            ])
            ->add('voicemail_asset', VoiceAssetAuthType::class, [
                'property_path' => 'voicemailAsset',
                'required'      => false,
            ])
            ->add('is_voice_enabled', ApiBooleanType::class, [
                'property_path' => 'isVoiceEnabled',
            ])
            ->add('outbound_calls_enabled', ApiBooleanType::class, [
                'property_path' => 'outboundCallsEnabled',
            ])
            ->add('available_status', ChoiceType::class, [
                'property_path'     => 'availableStatus',
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
            ->add('can_use_forwarding', ApiBooleanType::class, [
                'property_path' => 'canUseForwarding',
                'required'      => false,
            ])
            ->add('agent_can_use_forwarding', ApiBooleanType::class, [
                'property_path' => 'agentCanUseForwarding',
                'required'      => false,
            ])
            ->add('forwarding_number', TextType::class, [
                'property_path' => 'forwardingNumber',
                'required'      => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => AgentData::class,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['is_voice_enabled']) && !$data['is_voice_enabled']) {
            $data['available_status'] = AgentData::AVAILABLE_STATUS_OFFLINE;
        }
        if (isset($data['available_status']) && $data['available_status'] === AgentData::AVAILABLE_STATUS_OFFLINE) {
            $data['agent_calls_enabled'] = false;
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
        $form = $event->getForm();
        $data = $event->getData();

        if ($data instanceof AgentData) {
            $data->setPerson($form->getConfig()->getOption('person'));
        }
    }
}
