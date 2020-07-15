<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserChatQueueType.
 */
class UserChatQueueType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('routing_model', ChoiceType::class, [
                'required'          => true,
                'property_path'     => 'routingModel',
                'choices_as_values' => true,
                'choices'           => [
                    UserChatQueue::ROUTING_MODEL_ROUND_ROBIN,
                    UserChatQueue::ROUTING_MODEL_ROUND_ROBIN_OPTIONAL,
                    UserChatQueue::ROUTING_MODEL_LEAST_UTILIZED,
                    UserChatQueue::ROUTING_MODEL_SIMULRING,
                ],
            ])
            ->add('max_queue_size', IntegerType::class, [
                'required'      => true,
                'property_path' => 'maxQueueSize',
            ])
            ->add('answer_timeout', IntegerType::class, [
                'required'      => true,
                'property_path' => 'answerTimeout',
                'empty_data'    => '60',
            ])
            ->add('is_all_agents', ApiBooleanType::class, [
                'required'      => true,
                'property_path' => 'isAllAgents',
            ])
            ->add('targets', CollectionType::class, [
                'required'       => false,
                'entry_type'     => UserChatQueueTargetType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
                'error_bubbling' => false,
                'mapped'         => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $addedTargets = $event->getForm()->get('targets')->getData();
        $queue        = $event->getData();
        $existingKeys = [];
        $addingKeys   = [];

        //note: sorting is not affected here as it not updated at all and always is 10

        // collect unique keys which exist
        foreach ($queue->getTargets() as $existingTarget) {
            $key                = sprintf('%d-%d', $existingTarget->getAgent()->getId(), $existingTarget->getQueue()->getId());
            $existingKeys[$key] = true;
        }

        // add only those targets which don't exist and write down all keys we're trying to add
        foreach ($addedTargets as $target) {
            $target->setQueue($queue);
            $key              = sprintf('%d-%d', $target->getAgent()->getId(), $target->getQueue()->getId());
            $addingKeys[$key] = true;
            if (!isset($existingKeys[$key])) {
                $queue->getTargets()->add($target);
            }
        }

        // if existing targets are not in those we wanted to add - remove it from collection
        foreach ($queue->getTargets() as $target) {
            $key = sprintf('%d-%d', $target->getAgent()->getId(), $target->getQueue()->getId());
            if (!isset($addingKeys[$key])) {
                $queue->getTargets()->removeElement($target);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserChatQueue::class,
        ]);
    }
}
