<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Comments;

use Application\DeskPRO\Entity\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Entity\CommentAttachment\CommentAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\WebAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentAttachmentType.
 */
class CommentAttachmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return WebAttachmentType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'comment_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommentAttachment::class,
            ])
            ->setRequired('comment')
            ->setAllowedTypes('comment', CommentAbstract::class);
    }

    /**
     * @param FormEvent $event
     *
     * @internal
     */
    public function onSetRelations(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var CommentAbstract $comment */
        $comment = $form->getConfig()->getOption('comment');
        if ($data instanceof CommentAttachment) {
            $comment->addAttachment($data);
        }
    }
}
