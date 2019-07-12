<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Community;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\WebAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityTopicAttachmentType.
 */
class CommunityTopicAttachmentType extends AbstractType
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
        return 'community_topic_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommunityTopicAttachment::class,
            ])
            ->setRequired('topic')
            ->setAllowedTypes('topic', CommunityTopic::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        /** @var CommunityTopic $topic */
        $topic = $form->getConfig()->getOption('topic');
        if ($data instanceof CommunityTopicAttachment) {
            $topic->addAttachment($data);
        }
    }
}
