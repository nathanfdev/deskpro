<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Community;

use Application\DeskPRO\Entity\CommunityTopic;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\AttachmentCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityTopicAttachmentCollectionType.
 */
class CommunityTopicAttachmentCollectionType extends AbstractType
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
        return 'community_topic_attachment_collection';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'    => CommunityTopicAttachmentType::class,
                'entry_options' => function (Options $options) {
                    return [
                        'topic'  => $options['topic'],
                        'person' => $options['person'],
                        'label'  => false,
                    ];
                },
            ])
            ->setRequired('topic')
            ->setAllowedTypes('topic', CommunityTopic::class)
        ;
    }
}
