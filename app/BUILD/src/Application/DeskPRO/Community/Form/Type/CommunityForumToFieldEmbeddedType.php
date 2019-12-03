<?php

namespace Application\DeskPRO\Community\Form\Type;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\CommunityForumToCustomDefCommunityTopic;
use Application\DeskPRO\Entity\CustomDefCommunityTopic;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityForumToFieldEmbeddedType.
 */
class CommunityForumToFieldEmbeddedType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('field', EntityType::class, [
                'class' => CustomDefCommunityTopic::class,
            ])
            ->add('display_order', IntegerType::class)
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
        $event->getData()->setForum($event->getForm()->getConfig()->getOption('forum'));
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
            ->setRequired('forum')
            ->setAllowedTypes('forum', CommunityForum::class)
        ;
    }
}
