<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceQueueAgentCollectionType.
 */
class VoiceQueueAgentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'    => VoiceQueueAgentType::class,
                'entry_options' => function (Options $options) {
                    return [
                        'queue' => $options['queue'],
                    ];
                },
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
                'mapped'       => false,
            ])
            ->setRequired('queue')
            ->setAllowedTypes('queue', VoiceQueue::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = new ArrayCollection($event->getData());
        $form = $event->getForm();

        /** @var VoiceQueue $queue */
        $queue = $form->getConfig()->getOption('queue');

        foreach ($data as $voiceAgent) {
            $queue->addAgent($voiceAgent);
        }
        foreach ($queue->getAgents() as $voiceAgent) {
            $existVoiceAgent = $data->filter(function (VoiceQueueAgent $existVoiceAgent) use ($voiceAgent) {
                return $existVoiceAgent->getAgent() === $voiceAgent->getAgent();
            })->first();

            if (!$existVoiceAgent) {
                $queue->removeAgent($voiceAgent);
            }
        }
    }
}
