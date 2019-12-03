<?php

namespace Application\DeskPRO\CustomFields\Form\Type;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityForumToCustomDefCommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityFieldToForumType.
 */
class CommunityFieldToForumType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('forum', EntityType::class, [
                'class' => CommunityForum::class,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $event->getData()->setField($event->getForm()->getConfig()->getOption('custom_field'));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommunityForumToCustomDefCommunityTopic::class,
            ])
            ->setRequired('custom_field')
            ->setAllowedTypes('custom_field', CustomDefCommunityTopic::class)
        ;
    }
}
