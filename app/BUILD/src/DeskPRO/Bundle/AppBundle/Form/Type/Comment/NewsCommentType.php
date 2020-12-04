<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Comment;

use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsComment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class NewsCommentType.
 */
class NewsCommentType extends CommentType
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
                        'class' => NewsComment::class
                    ])
                    ->add('news', EntityType::class, [
                        'class'       => News::class,
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
            //validation to check that the provided parent_id is a comment of the provided news.
            if ((null !== $comment->getParent() && null !== $comment->getNews()) && $comment->getParent()->getNews()->getId() !== $comment->getNews()->getId()) {
                $event->getForm()->addError(new FormError('The Provided news and parent_id are not compatible'));
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewsComment::class,
        ]);
    }
}
