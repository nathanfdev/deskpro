<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Feedback;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\AttachmentCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackAttachmentCollectionType.
 */
class FeedbackAttachmentCollectionType extends AbstractType
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
        return 'feedback_attachment_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'    => FeedbackAttachmentType::class,
                'entry_options' => function (Options $options) {
                    return [
                        'feedback' => $options['feedback'],
                        'person'   => $options['person'],
                        'label'    => false,
                    ];
                },
            ])
            ->setRequired('feedback')
            ->setAllowedTypes('feedback', Feedback::class)
        ;
    }
}
