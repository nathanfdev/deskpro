<?php

namespace Application\DeskPRO\Community\Form\Type;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CommunityForum;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommunityForumPropsType.
 */
class CommunityForumPropsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', ['required' => true])
            ->add('description', 'text', ['required' => false])
            ->add('usergroups', 'entity', [
                'class'         => 'DeskPRO:Usergroup',
                'required'      => false,
                'expanded'      => true,
                'multiple'      => true,
                'property'      => 'title',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')->where(
                        'u.is_agent_group = false AND u.is_enabled = true'
                    );
                },
            ])
            ->add('topic_statuses', CollectionType::class, [
                'entry_type'    => CommunityForumToStatusEmbeddedType::class,
                'entry_options' => [
                    'forum' => $options['forum'],
                ],
                'allow_add'    => true,
                'allow_delete' => true,
            ])
            ->add('topic_fields', CollectionType::class, [
                'entry_type'    => CommunityForumToFieldEmbeddedType::class,
                'entry_options' => [
                    'forum' => $options['forum'],
                ],
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('color', 'text')
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CommunityForum::class,
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
        return 'community_forum';
    }
}
