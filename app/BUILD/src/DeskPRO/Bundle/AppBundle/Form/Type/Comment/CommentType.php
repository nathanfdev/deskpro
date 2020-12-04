<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Comment;

use Application\DeskPRO\Entity\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\PortalBundle\Helper\ChildCommentHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract Class CommentType.
 */
abstract class CommentType extends AbstractType
{
    /**
     * @var ChildCommentHelper
     */
    private $childCommentHelper;

    public function __construct(ChildCommentHelper $childCommentHelper)
    {
        $this->childCommentHelper = $childCommentHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', TextType::class, [
                'description' => 'text representation of comment status',
                'required'    => false,
            ])
            ->add('is_reviewed', ApiBooleanType::class, [
                'required'    => false,
                'description' => 'is comment was reviewed',
            ])->add('content', TextType::class, [
                'required'    => false,
                'description' => 'comment message',
            ]);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (null !== $data->getParent()) {
            $handledComment = $this->childCommentHelper->handleChildComment($data, false);

            if (!$handledComment instanceof CommentAbstract) {
                $event->getForm()->addError(new FormError($handledComment->getMessage()));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CommentAbstract::class,
        ]);
    }
}
