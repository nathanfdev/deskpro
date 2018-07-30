<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceQueueType.
 */
class VoiceQueueType extends AbstractType
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
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('department', EntityType::class, [
                'required'      => false,
                'property_path' => 'department',
                'class'         => Department::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where('u.is_tickets_enabled = 1');
                },
            ])
            ->add('agents', VoiceQueueAgentCollectionType::class, [
                'queue'    => $builder->getData(),
                'required' => true,
            ])
            ->add('greet_asset', VoiceAssetAuthType::class, [
                'property_path' => 'greetAsset',
                'required'      => false,
            ])
            ->add('loop_asset', VoiceAssetAuthType::class, [
                'property_path' => 'loopAsset',
                'required'      => false,
            ])
            ->add('voicemail_asset', VoiceAssetAuthType::class, [
                'property_path' => 'voicemailAsset',
                'required'      => false,
            ])
            ->add('voicemail_department', EntityType::class, [
                'required'      => false,
                'property_path' => 'voicemailDepartment',
                'class'         => Department::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where('u.is_tickets_enabled = 1');
                },
            ])
            ->add('voicemail_agent', EntityType::class, [
                'required'      => false,
                'property_path' => 'voicemailAgent',
                'class'         => Person::class,
            ])
            ->add('voicemail_agent_team', EntityType::class, [
                'required'      => false,
                'property_path' => 'voicemailAgentTeam',
                'class'         => AgentTeam::class,
            ])
            ->add('voicemail_timeout', IntegerType::class, [
                'required'      => false,
                'property_path' => 'voicemailTimeout',
            ])
            ->add('routing_model', ChoiceType::class, [
                'required'          => true,
                'property_path'     => 'routingModel',
                'choices_as_values' => true,
                'choices'           => [
                    VoiceQueue::ROUTING_MODEL_AUTOMATIC,
                    VoiceQueue::ROUTING_MODEL_LEAST_UTILIZED,
                    VoiceQueue::ROUTING_MODEL_SIMULRING,
                ],
            ])
            ->add('max_queue_size', IntegerType::class, [
                'required'      => true,
                'property_path' => 'maxQueueSize',
            ])
            ->add('recording_enabled', ApiBooleanType::class, [
                'required'      => false,
                'property_path' => 'recordingEnabled',
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
        $resolver->setDefaults([
            'data_class' => VoiceQueue::class,
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

        // set task queue max size
        if (isset($data['routing_model'])) {
            if ($data['routing_model'] === VoiceQueue::ROUTING_MODEL_AUTOMATIC) {
                $data['max_queue_size'] = 1;
            } elseif ($data['routing_model'] === VoiceQueue::ROUTING_MODEL_SIMULRING) {
                $data['max_queue_size'] = 50;
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
        if ($data instanceof VoiceQueue) {
            // make sure agent voice is enabled for queue agents
            foreach ($data->getAgents() as $voiceAgent) {
                $agent = $voiceAgent->getAgent();
                if (!$agent) {
                    continue;
                }

                $agentData = $agent->getAgentData();
                if (!$agentData) {
                    $agentData = new AgentData();
                    $agent->setAgentData($agentData);
                }

                $agentData->setIsVoiceEnabled(true);
                $agentData->setOutboundCallsEnabled(true);
            }
        }
    }
}
