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
            ])
        ;
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
