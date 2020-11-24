<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Comment;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicComment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CommunityTopicCommentType.
 */
class CommunityTopicCommentType extends CommentType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form    = $event->getForm();
            $comment = $event->getData();
            if (null === $comment->getId()) {
                $form
                    ->add('parent_id', EntityType::class, [
                        'class' => CommunityTopicComment::class
                    ])
                    ->add('topic', EntityType::class, [
                        'class'       => CommunityTopic::class,
                        'required'    => true,
                        'constraints' => [
                            new Assert\NotBlank(),
                            new Assert\NotNull(),
                        ],
                    ]);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $comment = $event->getData();
            //validation to check that the provided parent_id is a comment of the provided topic.
            if ((null !== $comment->getParent() && null !== $comment->getTopic()) && $comment->getParent()->getTopic()->getId() !== $comment->getTopic()->getId()) {
                $event->getForm()->addError(new FormError('The Provided topic and parent_id are not compatible'));
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommunityTopicComment::class,
        ]);
    }
}
