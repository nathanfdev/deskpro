<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAssetAuthType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                    AgentData::AVAILABLE_STATUS_IDLE_DISABLED,
                    AgentData::AVAILABLE_STATUS_BUSY,
                    AgentData::AVAILABLE_STATUS_RESERVED,
                    AgentData::AVAILABLE_STATUS_OFFLINE,
                ],
            ])
            ->add('agent_calls_enabled', ApiBooleanType::class, [
                'property_path' => 'agentCallsEnabled',
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
