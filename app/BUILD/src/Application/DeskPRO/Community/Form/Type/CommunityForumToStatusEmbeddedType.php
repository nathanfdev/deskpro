<?php

namespace Application\DeskPRO\Community\Form\Type;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityForumToStatus;
use Application\DeskPRO\Entity\CommunityTopicStatusCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityForumToStatusEmbeddedType.
 */
class CommunityForumToStatusEmbeddedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', EntityType::class, [
                'class' => CommunityTopicStatusCategory::class,
            ])
            ->add('display_order', IntegerType::class)
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    public function onPostSubmit(FormEvent $event)
    {
        $event->getData()->setForum($event->getForm()->getConfig()->getOption('forum'));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommunityForumToStatus::class,
            ])
            ->setRequired('forum')
            ->setAllowedTypes('forum', CommunityForum::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'community_forum_to_status_embedded';
    }
}
