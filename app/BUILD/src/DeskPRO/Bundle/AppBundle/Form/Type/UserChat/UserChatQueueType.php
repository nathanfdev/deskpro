<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\UserChat;

use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
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
use Symfony\Component\Validator\Constraints as Assert;

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
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 100);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (isset($data['is_all_agents']) && !$data['is_all_agents']) {
            $event->getForm()->add('targets', CollectionType::class, [
                'required'       => false,
                'entry_type'     => UserChatQueueTargetType::class,
                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
                'error_bubbling' => false,
                'mapped'         => false,
                'constraints'    => [
                    new Assert\Count(['min' => 1]),
                ],
            ]);
        }
    }

    /**
     * @param FormEvent $event
     *
     * @internal
     */
    public function onPostSubmit(FormEvent $event)
    {
        if ($event->getForm()->has('targets')) {
            $addedTargets = $event->getForm()->get('targets')->getData();
            /** @var UserChatQueue $queue */
            $queue        = $event->getData();
            $existingKeys = [];
            $addingKeys   = [];

            // collect unique keys which exist
            foreach ($queue->getTargets() as $existingTarget) {
                $existingKeys[$existingTarget->getAgent()->getId()] = $existingTarget;
            }

            // add only those targets which don't exist and write down all keys we're trying to add
            /** @var AbstractUserChatQueueTarget[] $addedTargets */
            foreach ($addedTargets as $target) {
                $targetId = $target->getAgent() ? $target->getAgent()->getId() : null;
                if ($targetId) {
                    $addingKeys[$targetId] = true;
                    if (!isset($existingKeys[$targetId])) {
                        $queue->addTarget($target);
                    } else {
                        $existingKeys[$targetId]->setSort($target->getSort());
                    }
                }
            }

            // if existing targets are not in those we wanted to add - remove it from collection
            foreach ($queue->getTargets() as $target) {
                if (!isset($addingKeys[$target->getAgent()->getId()])) {
                    $queue->getTargets()->removeElement($target);
                }
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
