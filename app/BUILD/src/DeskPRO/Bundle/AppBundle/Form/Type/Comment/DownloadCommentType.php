<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Comment;

use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadComment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DownloadCommentType.
 */
class DownloadCommentType extends CommentType
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
                        'class' => DownloadComment::class
                    ])
                    ->add('download', EntityType::class, [
                        'class'       => Download::class,
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
            //validation to check that the provided parent_id is a comment of the provided download.
            if ((null !== $comment->getParent() && null !== $comment->getDownload()) && $comment->getParent()->getDownload()->getId() !== $comment->getDownload()->getId()) {
                $event->getForm()->addError(new FormError('The Provided download and parent_id are not compatible'));
            }
        });

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DownloadComment::class,
        ]);
    }
}
