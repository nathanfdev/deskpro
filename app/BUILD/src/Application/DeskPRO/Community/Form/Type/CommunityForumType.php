<?php

namespace Application\DeskPRO\Community\Form\Type;

use Application\DeskPRO\Community\CommunityForumEdit;
use Application\DeskPRO\Entity\CommunityForum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityForumType.
 */
class CommunityForumType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('community_forum', CommunityForumPropsType::class, ['forum' => $options['forum']]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'         => CommunityForumEdit::class,
                'cascade_validation' => true,
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
        return 'community_forum_edit';
    }
}
