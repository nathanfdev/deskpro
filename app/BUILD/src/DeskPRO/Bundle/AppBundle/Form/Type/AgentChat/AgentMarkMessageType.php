<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\AgentChat;

use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AgentMarkMessageType.
 */
class AgentMarkMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ids', EntityType::class, [
                'class'         => AgentChatMessage::class,
                'multiple'      => true,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er
                        ->createQueryBuilder('u')
                        ->where('u.chat = :chat')
                        ->setParameter('chat', $options['chat'])
                    ;
                },
                'constraints' => [
                    new Assert\Count(['min' => 1]),
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    AgentChatMessage::STATUS_DELIVERED,
                    AgentChatMessage::STATUS_READ,
                ],
                'choices_as_values' => true,
                'constraints'       => [
                    new Assert\NotNull(),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['chat'])
            ->setAllowedTypes('chat', AgentChat::class)
        ;
    }
}
