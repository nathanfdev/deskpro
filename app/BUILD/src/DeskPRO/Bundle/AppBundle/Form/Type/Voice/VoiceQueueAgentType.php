<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class VoiceQueueAgentType.
 */
class VoiceQueueAgentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('agent', EntityType::class, [
                'class' => Person::class,
            ])
            ->add('is_enabled', ApiBooleanType::class, [
                'property_path' => 'isEnabled',
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => VoiceQueueAgent::class,
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
        $form = $event->getForm();
        $data = $event->getData();

        if ($data instanceof VoiceQueueAgent) {
            $data->setQueue($form->getConfig()->getOption('queue'));
        }
    }
}
