<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Comments;

use Application\DeskPRO\Entity\CommentAbstract;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\AttachmentCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentAttachmentCollectionType.
 */
class CommentAttachmentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AttachmentCollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'comment_attachment_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'    => CommentAttachmentType::class,
                'entry_options' => function (Options $options) {
                    return [
                        'comment' => $options['comment'],
                        'person'  => $options['person'],
                        'label'   => false,
                    ];
                },
            ])
            ->setRequired('comment')
            ->setAllowedTypes('comment', CommentAbstract::class);
    }
}
