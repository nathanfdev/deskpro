<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\CommunityTopicComment;

use DeskPRO\Bundle\AppBundle\Form\Type\MassActions\BaseMassActionsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FeedbackCommentMassActionsType.
 */
class CommunityTopicCommentMassActionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseMassActionsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'params_class' => CommunityTopicCommentMassActionParamsType::class,
        ]);
    }
}
